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

    private $logFilename = 'stock.log';

    private $attribute = 'stock_data';

    private $productResource;

    private $objectManager;

    public function __construct($filename)
    {
        $bootstrap = Bootstrap::create(BP, $_SERVER);

        $this->objectManager = $bootstrap->getObjectManager();

        $state = $this->objectManager->get('Magento\Framework\App\State');
        $state->setAreaCode('adminhtml');

        /** @var \Magento\Catalog\Model\ResourceModel\Product $resourceProduct */
        $this->productResource = $this->objectManager->get('Magento\Catalog\Model\ResourceModel\ProductFactory')->create();

        $this->filename = $filename;
    }

    private function process()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $_stockRegistry = $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');

        $csvFile = file($this->filename);
        $logFile = fopen($this->logFilename, 'w');

        foreach ($csvFile as $line) {
            $data = null;
            $data = str_getcsv($line);
            try {
                if(isset($data[1])){
                    $isInstock = ($data[1] == 0) ? 0 : 1;

                }
                else{
                    continue;
                }

                $_stockItem = $_stockRegistry->getStockItem($data[0]);
                $_stockItem->setData('is_in_stock',$isInstock); //set updated data as your requirement
                $_stockItem->setData('qty', $data[1]); //set updated quantity
                $_stockItem->save(); //save stock of item

                echo $data[0] . PHP_EOL;
                fwrite($logFile, $data[0] . ' - updated' . PHP_EOL);
            } catch (Exception $e) {
                echo $data[0] . ' - ' . $e->getMessage() . PHP_EOL;
                fwrite($logFile, $data[0] . ' - ' . $e->getMessage() . PHP_EOL);
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
