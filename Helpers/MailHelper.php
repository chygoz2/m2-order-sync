<?php
namespace Gloo\OrderStatusSync\Helpers;

class MailHelper {

    public $logger;
    public $env;
    public $transportBuilder;
    public $escaper;


    public function __construct(
        \Magento\Framework\App\DeploymentConfig $env,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->logger = $logger;
        $this->env = $env;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
    }

    public function sendEmail($message){
        try {
            $sender = [
                'name' => $this->escaper->escapeHtml($this->env->get('custom/error_sender_name')),
                'email' => $this->escaper->escapeHtml($this->env->get('custom/error_sender_email')),
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
                ->addTo($this->env->get('custom/error_recipient_email'))
                ->getTransport();
            $transport->sendMessage();

        } catch(\Exception $e){
            $this->logger->critical($e->getMessage()); 
        }
    }
}