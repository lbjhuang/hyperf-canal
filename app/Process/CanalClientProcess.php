<?php

declare(strict_types=1);

namespace App\Process;

use Com\Alibaba\Otter\Canal\Protocol\EntryType;
use Com\Alibaba\Otter\Canal\Protocol\EventType;
use Com\Alibaba\Otter\Canal\Protocol\RowData;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use xingwenge\canal_php\CanalConnectorFactory;
use xingwenge\canal_php\CanalClient;
use xingwenge\canal_php\Fmt;
use Com\Alibaba\Otter\Canal\Protocol\RowChange;
/**
 * @Process(name="CanalProcess")
 */
class CanalClientProcess extends AbstractProcess
{
    public function handle(): void
    {
        try {
            #canal客户端连接
            $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SOCKET_CLUE);
            $canal_config = config('canal');
            # $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SWOOLE);
            $client->connect($canal_config['canal_host'], $canal_config['canal_port']); # 对应 canal.properties的配置
            $client->checkValid();
            //$client->subscribe("1001", "example", ".*\\..*"); #此处1001不需要修改，example 是在canal配置文件里配置的名称
            $client->subscribe($canal_config['canal_client_id'], $canal_config['canal_destination'], implode(',', $canal_config['listen_tables'])); # 设置过滤表,逗号隔开
            # $client->subscribe("1001", "example", "db_name.tb_name"); # 设置过滤,多个数据库用逗号隔开
            # $client->subscribe("1001", "example", "db_name.tb_name_[0-9]"); # 可以批量设置分表表名

            while (true) {
                $message = $client->get(100);
                if ($entries = $message->getEntries()) {
                    foreach ($entries as $entry) {
                        //Fmt::println($entry);
                        $rowChange = new RowChange();
                        $rowChange->mergeFromString($entry->getStoreValue());
                        $evenType = $rowChange->getEventType();
                        $header = $entry->getHeader();
                        $database_name = $header->getSchemaName();
                        $table_name = $header->getTableName();

                        /** @var RowData $rowData */
                        foreach ($rowChange->getRowDatas() as $rowData) {
                            $data = [];
                            $data['database_name'] = $database_name;
                            $data['table_name'] = $table_name;
                            $data['type'] = $evenType;

                            $keyIndex = [];
                            if($evenType = EventType::UPDATE){
                                foreach ($rowData->getBeforeColumns() as $column) {
                                    #获取主键 无主键不更新
                                    if ($column->getIsKey()) {
                                        $keyIndex['keyName'] = $column->getName();
                                        $keyIndex['keyValue'] = $column->getValue();
                                    }

                                    $data['old_data'][$column->getName()] = ['value' => $column->getValue(), 'updated' => $column->getUpdated()];
                                }

                                foreach ($rowData->getAfterColumns() as $column) {
                                    if ($column->getIsNull()) {
                                        $data['new_data'][$column->getName()] = ['value' => null, 'updated' => $column->getUpdated()];
                                    } else {
                                        $data['new_data'][$column->getName()] = ['value' => $column->getValue(), 'updated' => $column->getUpdated()];
                                    }
                                }
                                print_r($data);return;
                            }
                        }
                    }
                }
                sleep(1);
            }
            $client->disConnect();
        } catch (\Exception $e) {
            echo $e->getMessage(), PHP_EOL;
        }
    }

}
