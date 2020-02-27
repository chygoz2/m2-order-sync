<?php
namespace Gloo\OrderStatusSync\Cron;

use Zend\Json\Json;

class UpdateOrderStatusInCore {

    public $logger;
    public $iterator;
    public $orderFactory;
    public $httpClient;
    public $env;
    public $transportBuilder;
    public $escaper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Model\ResourceModel\Iterator $iterator,
        \Gloo\OrderStatusSync\Model\OrderFactory $orderFactory,
        \Zend\Http\Client $httpClient,
        \Magento\Framework\App\DeploymentConfig $env,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    )
    {
        $this->logger = $logger;
        $this->iterator = $iterator;
        $this->orderFactory = $orderFactory;
        $this->httpClient = $httpClient;
        $this->env = $env;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
    }

    public function execute()
    {
        try {
            $orderCollection = $this->orderFactory->create()->getCollection();
            $this->iterator
                ->walk(
                    $orderCollection->getSelect(),
                    [[$this, 'syncWithCore']]
                );
            $this->logger->info("An attempt to sync data with core was made ==== ORDER STATUS SYNC MODULE");
    
          } catch(\Exception $e){
              $this->logger->critical('There was an error syncing with core, a retry will be attempted in the next sync ======> '. $e->getMessage());
          }
    
    }

    public function syncWithCore($order){
        $magentoCoreSecret = $this->env->get('custom/magento_core_secret');
        $order['row']['magento_core_secret'] = $magentoCoreSecret;
        $params = $order['row'];
        $incrementId = $order['row']['increment_id'];
        $coreUri = $this->env->get('custom/core_uri');

        try {
            $this->httpClient->reset();
            $this->httpClient->setUri($coreUri);
            $this->httpClient->setMethod('POST');
            $this->httpClient->setHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
               ]);
            $this->httpClient->setRawBody(Json::encode($params));
       	    $this->httpClient->send();
            $response = $this->httpClient->getResponse();
    
            $statusCode = $response->getStatusCode();
            $orderFactory = $this->orderFactory->create();
            $order = $orderFactory->load($incrementId, 'increment_id');
    
            if($statusCode !== 201 && $statusCode !== 200){
                $tries = $orderFactory->getTries();
                $orderFactory->setTries($tries + 1);
                $orderFactory->save();
                if(($tries + 1) > 10){
                    $this->sendEmail("Order with increment id {$incrementId} failed to sync with core after more than ten attempt with a status $statusCode, kindly investigate");
                }
                $this->logger->critical("Order with increment id {$incrementId} failed to sync with core, an attempt will be made in the next sync === failed with status $statusCode");
            } else {
                $order->delete();
        
                $this->logger->info('sync with core '.json_encode($params));
            }
        } catch (\Zend\Http\Exception\RuntimeException $runtimeException){
            $this->logger->critical($runtimeException->getMessage());   
        }
    }

        public function sendEmail($message){
            try {
                $sender = [
                    'name' => $this->escaper->escapeHtml('Engineering'),
                    'email' => $this->escaper->escapeHtml('info@gloopro.com'),
                ];
                $transport = $this->transportBuilder
                    ->setTemplateIdentifier('send_email_email_template')
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        ]
                    )
                    ->setTemplateVars([
                        'message'  => $message,
                    ])
                    ->setFrom($sender)
                    ->addTo('engineering@gloopro.com')
                    ->getTransport();
                $transport->sendMessage();

            } catch(\Exception $e){
                $this->logger->critical($e->getMessage()); 
            }
        }
}