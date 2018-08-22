<?php

namespace Sylake\AkeneoProducerBundle\Command;

use Akeneo\Bundle\ClassificationBundle\Doctrine\ORM\Repository\CategoryRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Akeneo\Component\Classification\Model\CategoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;

final class ExportCategoriesCommand extends Command
{
    /** @var CategoryRepository */
    private $categoryRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * ExportCategoriesCommand constructor.
     *
     * @param CategoryRepository $categoryRepository
     * @param EntityManager      $entityManager
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        EntityManager $entityManager
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;

        parent::__construct('sylake:producer:export-categories');
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
        /** @var CategoryInterface[] $categories */
        $categories = $this->categoryRepository->findAll();

        $start = $input->getArgument('start');
        $started = empty($start);

        foreach ($categories as $category) {
            if ($category->getId() == $start) {
                $started = true;
            }

            if (!$started) {
                continue;
            }

            $output->writeln(sprintf('Exporting category "%s".', $category->getId()));

            $this->triggerPostPersist($category);
            $this->triggerFlushEvent();
        }
    }

    /**
     * Post persist event triggeren
     *
     * @param CategoryInterface $category
     */
    private function triggerPostPersist(CategoryInterface $category)
    {
        $eventManager = $this->entityManager->getEventManager();
        $eventArgs = new LifecycleEventArgs($category, $this->entityManager);
        $eventManager->dispatchEvent(Events::postPersist, $eventArgs);
    }

    /**
     * Flush event triggeren
     */
    private function triggerFlushEvent()
    {
        $eventManager = $this->entityManager->getEventManager();
        $postEventArgs = new PostFlushEventArgs($this->entityManager);
        $eventManager->dispatchEvent(Events::postFlush, $postEventArgs);
    }
}