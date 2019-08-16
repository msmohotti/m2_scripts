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

    private $logFilename = 'attribute_update.log';

    private $attribute = 'tco_sell_seller_share';

    private $productRepository;

    private $productResource;

    private $objectManager;

    public function __construct()
    {
        $bootstrap = Bootstrap::create(BP, $_SERVER);

        $this->objectManager = $bootstrap->getObjectManager();

        $state = $this->objectManager->get('Magento\Framework\App\State');
        $state->setAreaCode('adminhtml');

        /** @var Magento\Catalog\Model\ProductRepository $productRepository */
        $this->productRepository = $this->objectManager->get('Magento\Catalog\Model\ProductRepository');

        /** @var \Magento\Catalog\Model\ResourceModel\Product $resourceProduct */
        $this->productResource = $this->objectManager->get('Magento\Catalog\Model\ResourceModel\ProductFactory')->create();

    }

    private function process()
    {
        $csvFile = file($this->filename);
        $logFile = fopen($this->logFilename, 'w');

        $stores = array(1,8);

        foreach ($csvFile as $line) {
            $data = null;
            $data = str_getcsv($line);
            try {
                $product = $this->productRepository->getById($data[0]);
                foreach ($stores as $store) {
                    $product->setData('store_id', $store);
                    $product->setData($this->attribute, $data[1]);
                    $this->productResource->saveAttribute($product, $this->attribute);
                    echo $data[0]  . ' - ' . $store . PHP_EOL;
//                        usleep( 100000 );
                }

                fwrite($logFile, $data[0] . ' - updated - ' . $store . PHP_EOL);
            } catch (Exception $e) {
                echo $data[0] . ' - ' . $e->getMessage() . PHP_EOL;
                fwrite($logFile, $data[0] . ' - error - '  . $store . ' ' . $e->getMessage() . PHP_EOL);
            }
        }

    }

    public function run()
    {
        $this->process();
    }
}

$dataCsv = new DataImportCsv();
$dataCsv->run();