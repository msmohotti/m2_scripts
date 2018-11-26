<?php

use Magento\Framework\App\Bootstrap;

require __DIR__ . '/app/bootstrap.php';

/**
 * Class DataImportCsv
 *
 * products.csv sample
 * 21603, attribute value
 */
class DataImportCsv
{
    private $filename = 'hashing-magento-emails.csv';

    private $logFilename = 'final.log';

    private $attribute = 'size';

    private $productRepository;

    private $productResource;

    private $objectManager;

    public function __construct($filename)
    {
        $bootstrap = Bootstrap::create(BP, $_SERVER);

        $this->objectManager = $bootstrap->getObjectManager();

        $state = $this->objectManager->get('Magento\Framework\App\State');
        $state->setAreaCode('adminhtml');

        /** @var Magento\Catalog\Model\ProductRepository $productRepository */
        $this->productRepository = $this->objectManager->get('Magento\Catalog\Model\ProductRepository');

        /** @var \Magento\Catalog\Model\ResourceModel\Product $resourceProduct */
        $this->productResource = $this->objectManager->get('Magento\Catalog\Model\ResourceModel\ProductFactory')->create();

        $this->filename = $filename;
    }

    private function process()
    {
        $csvFile = file($this->filename);
        $logFile = fopen($this->logFilename, 'w');

        foreach ($csvFile as $line) {
            try {
                $data = null;
                $data = str_getcsv($line);

                fwrite($logFile, md5($data[1]) . ',' . $data[1] . ',' . $data[2] . ',' . $data[3] . ',' . $data[4] . PHP_EOL);
            } catch (Exception $e) {
                echo $data[1] . ' - ' . $e->getMessage() . PHP_EOL;
            }
        }
    }

    public function run()
    {
        $this->process();
    }
}

$dataCsv = new DataImportCsv('hashing-magento-emails.csv');
$dataCsv->run();
