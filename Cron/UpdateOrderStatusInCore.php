<?php
namespace Gloo\OrderStatusSync\Cron;

use Zend\Json\Json;

class UpdateOrderStatusInCore {

    public $logger;
    public $iterator;
    public $orderFactory;
    public $httpClient;
    public $dataHelper;
    public $mailHelper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Model\ResourceModel\Iterator $iterator,
        \Gloo\OrderStatusSync\Model\OrderFactory $orderFactory,
        \Gloo\OrderStatusSync\Helpers\MailHelper $mailHelper,
        \Zend\Http\Client $httpClient,
        \Gloo\OrderStatusSync\Helpers\Data $dataHelper
    )
    {
        $this->logger = $logger;
        $this->iterator = $iterator;
        $this->orderFactory = $orderFactory;
        $this->httpClient = $httpClient;
        $this->dataHelper = $dataHelper;
        $this->mailHelper = $mailHelper;
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
        $magentoCoreSecret = $this->dataHelper->getGeneralConfig('core_secret');
        $params = $order['row'];
        $incrementId = $params['increment_id'];
        $coreUri = $this->dataHelper->getGeneralConfig('core_url');
        $tries = $params['tries'];

        if($tries > 10){
            return;
        }

        try {
            $this->httpClient->reset();
            $this->httpClient->setUri($coreUri);
            $this->httpClient->setMethod('POST');
            $this->httpClient->setHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Secret' => $magentoCoreSecret
               ]);
            $this->httpClient->setRawBody(Json::encode($params));
       	    $this->httpClient->send();
            $response = $this->httpClient->getResponse();
    
            $statusCode = $response->getStatusCode();
            $orderFactory = $this->orderFactory->create();
            $order = $orderFactory->load($incrementId, 'increment_id');
    
            if($statusCode !== 201 && $statusCode !== 200){
                $orderFactory->setTries($tries + 1);
                $orderFactory->save();
                if(($tries + 1) === 10){
                    $this->mailHelper->sendEmail("Order with increment id {$incrementId} failed to sync with core after more than ten attempts with a status $statusCode, kindly investigate");
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
}