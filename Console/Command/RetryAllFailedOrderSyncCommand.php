<?php
   namespace Gloo\OrderStatusSync\Console\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
    use Gloo\OrderStatusSync\Model\OrderFactory;

    /**
     * Class ListAllFailedOrderSyncCommand
     */
    class RetryAllFailedOrderSyncCommand extends Command
    {
        const IncrementId='IncrementId';
        public $orderFactory;

        /**
         * @inheritDoc
         */
        protected function configure()
        {
            $options = [
                new InputOption(
                    self::IncrementId,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'IncrementId'
                )
           ];
           $this->setDescription('Retry all failed order sync or a specific order using the --IncrementId flag');
           $this->setDefinition($options);

           parent::configure();
        }

        /**
         * Execute the command
         *
         * @param InputInterface $input
         * @param OutputInterface $output
         *
         * @return null
         */
        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('Gloo\OrderStatusSync\Model\Order');
            if ($incrementId = $input->getOption(self::IncrementId)) {
                try {
                    $order->load($incrementId, 'increment_id');
                    $entity_id = $order->getEntityId();
                    if($entity_id){
                        $order->setTries(0);
                        $order->save();
                        $output->writeln("<info>Order with increment id {$incrementId} was set to retry successfully</info>");
                    } else {
                        $output->writeln("<error>The order with the increment id {$incrementId} don't exist</error>");
                    }

                }catch(\Exception $e){
                    $message = $e->getMessage();
                    $output->writeln("<error>An error encountered ===> {$message}</error>");
                }
            } else {
                try {
                    $orderCollection = $order->getCollection()->getSelect()->where("tries > 0");
                    $iterator = $objectManager->create('Magento\Framework\Model\ResourceModel\Iterator');
                    $iterator
                        ->walk(
                        $orderCollection,
                        [[$this, 'processOrders']]
                    );
                    $output->writeln("done walking");
                } catch(\Exception $e){
                    $message = $e->getMessage();
                    $output->writeln("<error>An error encountered ===> {$message}</error>");
                }
            }
        }

        public function processOrders($order){
            $entity_id = $order['row']['entity_id'];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('Gloo\OrderStatusSync\Model\Order');
            $order->load($entity_id);
            $order->setTries(0);
            $order->save();
        }
    }