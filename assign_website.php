<?php

use Magento\Framework\App\Bootstrap;

require __DIR__ . '/app/bootstrap.php';

/**
 * Class DataImportCsv
 *
 * products.csv sample
 * 21603
 */
class DataImportCsv
{
    private $filename = 'products.csv';

    private $logFilename = 'attribute_update.log';

    private $objectManager;

    public function __construct()
    {
        $bootstrap = Bootstrap::create(BP, $_SERVER);

        $this->objectManager = $bootstrap->getObjectManager();

        $state = $this->objectManager->get('Magento\Framework\App\State');
        $state->setAreaCode('adminhtml');
    }

    private function process()
    {
        $csvFile = file($this->filename);
        $logFile = fopen($this->logFilename, 'w');

        $website_id = 2;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = 'catalog_product_website';

        foreach ($csvFile as $line) {
            $data = null;
            $data = str_getcsv($line);
            try {
                //Select Data from table
                $sql = "SELECT * FROM " . $tableName . " WHERE product_id = {$data[0]} AND website_id = {$website_id}";
                $result = $connection->fetchAll($sql); // gives associated array, table fields as key in array.

                if(empty($result)){
                    //Insert Data into table
                    $sql = "INSERT INTO {$tableName} (`product_id`, `website_id`) VALUES ({$data[0]}, {$website_id})";
                    $connection->query($sql);

                    fwrite($logFile, $data[0] . ' - updated' . PHP_EOL);
                }
                echo $data[0] . PHP_EOL;

            } catch (Exception $e) {
                echo $data[0] . ' - ' . $e->getMessage() . PHP_EOL;
                fwrite($logFile, $data[0] . ' - error - ' . ' ' . $e->getMessage() . PHP_EOL);
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