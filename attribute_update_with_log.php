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
    private $filename = 'products.csv';

    private $logFilename = 'import.log';

    private $attribute = 'cost';

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
        $this->productRepository = $this->objectManager->get('\Magento\Catalog\Model\ProductFactory');

        /** @var \Magento\Catalog\Model\ResourceModel\Product $resourceProduct */
        $this->productResource = $this->objectManager->get('Magento\Catalog\Model\ResourceModel\ProductFactory')->create();

        $this->filename = $filename;

        $this->logFilename = 'import_' . $this->attribute . '.log';
    }

    private function process()
    {
        $csvFile = file($this->filename);
        $logFile = fopen($this->logFilename, 'w');

        fwrite($logFile, $this->attribute . PHP_EOL);
        fwrite($logFile, 'id,old,new' . PHP_EOL);

        foreach ($csvFile as $line) {
            $data = null;
            $data = str_getcsv($line);
            try {
                $product= $this->productRepository->create()->setStoreId(0)->load($data[0]);

                fwrite($logFile, $data[0] . ',' .$product->getData($this->attribute) . ',' . $data[1] . ',updated' . PHP_EOL);
                $product->setData('store_id', 0);
                $product->setData($this->attribute, $data[1]);
                $this->productResource->saveAttribute($product, $this->attribute);
                echo $data[0] . PHP_EOL;
            } catch (Exception $e) {
                echo $data[0] . ' - ' . $e->getMessage() . PHP_EOL;
                fwrite($logFile, $data[0] . ',' . ',' . $data[1] . ',error' . PHP_EOL);

            }
        }
    }

    public function run()
    {
        $this->process();
    }
}

$dataCsv = new DataImportCsv('products.csv');
$dataCsv->run();
