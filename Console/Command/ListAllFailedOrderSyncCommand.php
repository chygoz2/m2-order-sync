<?php
   namespace Gloo\OrderStatusSync\Console\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Gloo\OrderStatusSync\Model\OrderFactory;

    /**
     * Class ListAllFailedOrderSyncCommand
     */
    class ListAllFailedOrderSyncCommand extends Command
    {
        public $output = null;

        /**
         * @inheritDoc
         */
        protected function configure()
        {
           $this->setDescription('List all failed order sync');

           parent::configure();
        }

        /**
         * Execute the command
         *
         * @param InputInterface $input
         * @param OutputInterface $output
         *
         * @return null|int
         */
        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('Gloo\OrderStatusSync\Model\Order');
            $this->output = $output;
            $this->output->writeln("<info>List of orders that failed to sync</info>");
            $this->output->writeln("===============================================");
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

        public function processOrders($order){
            $entity_id = $order['row']['entity_id'];
            $incrementId = $order['row']['increment_id'];
            $this->output->writeln("<info>| {$incrementId} failed to sync with core |</info>");
        }
    }