<?php
namespace Gloo\OrderStatusSync\Cron;

use Zend\Json\Json;

class UpdateOrderStatusInCore {

    public $logger;
    public $iterator;
    public $orderFactory;
    public $httpClient;
    public $env;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Model\ResourceModel\Iterator $iterator,
        \Gloo\OrderStatusSync\Model\OrderFactory $orderFactory,
        \Zend\Http\Client $httpClient,
        \Magento\Framework\App\DeploymentConfig $env 
    )
    {
        $this->logger = $logger;
        $this->iterator = $iterator;
        $this->orderFactory = $orderFactory;
        $this->httpClient = $httpClient;
        $this->env = $env;
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
    
            if($statusCode !== 201 && $statusCode !== 200){
                $this->logger->critical("Order with increment id {$incrementId} failed to sync with core, an attempt will be made in the next sync === failed with status $statusCode");
            } else {
                $orderFactory = $this->orderFactory->create();
                $order = $orderFactory->load($incrementId, 'increment_id');
                $order->delete();
        
                $this->logger->info('sync with core '.json_encode($params));
            }
        } catch (\Zend\Http\Exception\RuntimeException $runtimeException){
            $this->logger->critical($runtimeException->getMessage());   
        }
    }
}