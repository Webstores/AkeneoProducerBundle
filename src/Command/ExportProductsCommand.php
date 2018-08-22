<?php

namespace Sylake\AkeneoProducerBundle\Command;

use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSaver;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Common\Persistence\ObjectManager;

final class ExportProductsCommand extends Command
{
    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /** @var ProductSaver */
    private $productSaver;

    /** @var ObjectManager */
    private $objectManager;

    /**
     * ExportProductsCommand constructor.
     *
     * @param ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
     * @param ProductSaver                        $productSaver
     * @param ObjectManager                       $objectManager
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ProductSaver $productSaver,
        ObjectManager $objectManager
    ) {
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->productSaver = $productSaver;
        $this->objectManager = $objectManager;

        parent::__construct('sylake:producer:export-products');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument('start', InputArgument::OPTIONAL)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productQueryBuilder = $this->productQueryBuilderFactory->create();
        $productQueryBuilder->addSorter('sku', 'ASC');

        $products = $productQueryBuilder->execute();

        $start = $input->getArgument('start');
        $started = empty($start);

        /** @var ProductInterface $product */
        foreach ($products as $product) {
            if ($product->getIdentifier() == $start) {
                $started = true;
            }

            if (!$started) {
                $this->objectManager->detach($product);
                continue;
            }

            $output->writeln(sprintf('Exporting product with SKU "%s".', $product->getIdentifier()));

            $product->setUpdated(new \DateTime());

            $this->productSaver->save($product);

            // Memory leak probleem verholpen door producten na opslaan te verwijderen uit de object manager
            $this->objectManager->detach($product);
        }
    }
}