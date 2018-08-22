<?php

namespace Sylake\AkeneoProducerBundle\Command;

use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSaver;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ExportProductCommand extends Command
{
    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;


    /** @var ProductSaver */
    private $productSaver;

    /**
     * @param ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
     * @param ProductSaver                        $productSaver
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ProductSaver $productSaver
    ) {
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->productSaver = $productSaver;

        parent::__construct('sylake:producer:export-product');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument('sku', InputArgument::REQUIRED)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sku = $input->getArgument('sku');

        $productQueryBuilder = $this->productQueryBuilderFactory->create();
        $productQueryBuilder->addFilter('sku', '=', $sku);

        $products = $productQueryBuilder->execute();

        if (count($products) === 0) {
            $output->writeln(sprintf('<error>Could not find product with SKU "%s"!</error>', $sku));

            return 1;
        }

        /** @var ProductInterface $product */
        foreach ($products as $product) {
            $output->writeln(sprintf('Exporting product with SKU "%s".', $sku));

            $product->setUpdated(new \DateTime());

            $this->productSaver->save($product);
        }
    }
}