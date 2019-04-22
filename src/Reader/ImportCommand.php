<?php declare(strict_types=1);
namespace App\Reader;

use App\Entity\Order;
use ArrayObject;
use InvalidArgumentException;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Reader\XPath;
use App\Reader\XmlData;
use App\Reader\DataReader;
use App\Reader\DataTransformer;

class ImportCommand extends Command
{
    use XPath;

    /** @var RegistryInterface */
    private $registry;

    /** @var DataReader */
    private $reader;

    /** @var DataTransformer\Order */
    private $orderTransformer;

    public function __construct(RegistryInterface $registry, DataReader $reader, DataTransformer\Order $transformer)
    {
        parent::__construct('import:xml');

        $this->reader = $reader;
        $this->registry = $registry;
        $this->orderTransformer = $transformer;
    }

    public function configure(): void
    {
        $this->addArgument('file', InputArgument::OPTIONAL, 'File to read', __DIR__ . '/../../var/resources/orders-test.xml');
    }

    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $manager = $this->registry->getManagerForClass(Order::class);
        $file = $input->getArgument('file');

        assert(is_string($file));
        assert($manager !== null);

        // fetch relations first
        foreach ($this->reader->read($file) as $data) {
            $order = $this->orderTransformer->transform($data);

            $manager->persist($order);
            $manager->flush();

            $manager->clear();
        }
    }
}

