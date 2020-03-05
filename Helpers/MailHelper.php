<?php
namespace Gloo\OrderStatusSync\Helpers;

class MailHelper {

    public $logger;
    public $dataHelper;
    public $transportBuilder;
    public $escaper;


    public function __construct(
        \Gloo\OrderStatusSync\Helpers\Data $dataHelper,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
    }

    public function sendEmail($message){
        try {
            $sender = [
                'name' => $this->dataHelper->getGeneralConfig('error_sender_name'),
                'email' => $this->dataHelper->getGeneralConfig('error_sender_email'),
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
                ->addTo($this->dataHelper->getGeneralConfig('error_recipient_email'))
                ->getTransport();
            $transport->sendMessage();

        } catch(\Exception $e){
            $this->logger->critical($e->getMessage()); 
        }
    }
}