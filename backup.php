<?php

/**
 * Class Backup
 * @author Tayfun Erbilen
 */
class Backup
{

    private $config = [];
    private $db;
    private $sql;

    /**
     * Backup constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param array $config
     * @return bool|int
     */
    public function mysql($config = [])
    {
        if ($config)
            $this->config['db'] = $config;

        /**
         * Veritabanına bağlan
         */
        try {
            $this->db = new PDO(
                'mysql:host=' . $this->config['db']['host'] . ';dbname=' . $this->config['db']['dbname'] . ';charset=utf8',
                $this->config['db']['user'],
                $this->config['db']['pass']
            );
        } catch (PDOException $e) {
            die($e->getMessage());
        }

        $tables = $this->getAll('SHOW TABLES');

        foreach ($tables as $table) {

            $tableName = current($table);

            /**
             * Tablo satırları
             */
            $rows = $this->getAll('SELECT * FROM %s', [$tableName]);

            $this->sql .= '-- Tablo Adı: ' . $tableName . "\n-- Satır Sayısı: " . count($rows) . str_repeat(PHP_EOL, 2);

            /**
             * Tablo detayları
             */
            $tableDetail = $this->getFirst('SHOW CREATE TABLE %s', [$tableName]);
            $this->sql .= $tableDetail['Create Table'] . ';' . str_repeat(PHP_EOL, 3);

            /**
             * Satır sayısı 0dan büyükse
             */
            if (count($rows) > 0) {

                $columns = $this->getAll('SHOW COLUMNS FROM %s', [$tableName]);
                $columns = array_map(function ($column) {
                    return $column['Field'];
                }, $columns);

                // INSERT INTO kategoriler (kategori_id, kategori_adi) VALUES (1,'test'), (2, 'test2')

                $this->sql .= 'INSERT INTO `' . $tableName . '` (`' . implode('`,`', $columns) . '`) VALUES ' . PHP_EOL;

                $columnsData = [];
                foreach ($rows as $row) {
                    $row = array_map(function ($item) {
                        return $this->db->quote($item);
                    }, $row);
                    $columnsData[] = '(' . implode(',', $row) . ')';
                }
                $this->sql .= implode(',' . PHP_EOL, $columnsData) . ';' . str_repeat(PHP_EOL, 5);

            }

        }

        // Triggerlar için metod
        $this->dumpTriggers();

        // Fonksiyonlar için metod
        $this->dumpFunctions();

        // Procedure için metod
        $this->dumpProcedures();

        return file_put_contents($this->config['db']['file'], $this->sql);

    }

    /**
     * @param $query
     * @param array $params
     * @return mixed
     */
    private function getFirst($query, $params = [])
    {
        return $this->db->query(vsprintf($query, $params))->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $query
     * @param array $params
     * @return mixed
     */
    private function getAll($query, $params = [])
    {
        return $this->db->query(vsprintf($query, $params))->fetchAll(PDO::FETCH_ASSOC);
    }

    private function dumpTriggers()
    {

        $triggers = $this->getAll('SHOW TRIGGERS');
        if (count($triggers) > 0) {
            $this->sql .= '-- TRIGGERS (' . count($triggers) . ')' . str_repeat(PHP_EOL, 2);
            $this->sql .= 'DELIMITER //' . PHP_EOL;
            foreach ($triggers as $trigger) {
                $query = $this->getFirst('SHOW CREATE TRIGGER %s', [$trigger['Trigger']]);
                $this->sql .= $query['SQL Original Statement'] . '//' . PHP_EOL;
            }
            $this->sql .= 'DELIMITER ;' . str_repeat(PHP_EOL, 5);
        }

    }

    private function dumpFunctions()
    {

        $functions = $this->getAll('SHOW FUNCTION STATUS WHERE Db = "%s"', [$this->config['db']['dbname']]);
        if (count($functions) > 0) {
            $this->sql .= '-- FUNCTIONS (' . count($functions) . ')' . str_repeat(PHP_EOL, 2);
            $this->sql .= 'DELIMITER //' . PHP_EOL;
            foreach ($functions as $function) {
                $query = $this->getFirst('SHOW CREATE FUNCTION %s', [$function['Name']]);
                $this->sql .= $query['Create Function'] . '//' . PHP_EOL;
            }
            $this->sql .= 'DELIMITER ;' . str_repeat(PHP_EOL, 5);
        }

    }

    private function dumpProcedures()
    {

        $procedures = $this->getAll('SHOW PROCEDURE STATUS WHERE Db = "%s"', [$this->config['db']['dbname']]);
        if (count($procedures) > 0) {
            $this->sql .= '-- PROCEDURES (' . count($procedures) . ')' . str_repeat(PHP_EOL, 2);
            $this->sql .= 'DELIMITER //' . PHP_EOL;
            foreach ($procedures as $procedure) {
                $query = $this->getFirst('SHOW CREATE PROCEDURE %s', [$procedure['Name']]);
                $this->sql .= $query['Create Procedure'] . '//' . PHP_EOL;
            }
            $this->sql .= 'DELIMITER ;' . str_repeat(PHP_EOL, 5);
        }

    }

    /**
     * @param $dir
     * @return array
     */
    private function getDirectory($dir)
    {
        static $files = [];
        foreach (glob($dir . '/*') as $file) {
            $notInclude = !in_array(str_replace($this->config['folder']['dir'] . '/', null, $file), $this->config['folder']['exclude']);
            if (
                is_dir($file) &&
                $notInclude
            ) {
                call_user_func([$this, 'getDirectory'], $file);
            } else {
                if ($notInclude)
                    $files[] = $file;
            }
        }
        return $files;
    }

    /**
     * @param array $config
     * @return bool
     * @throws Exception
     */
    public function folder($config = [])
    {

        if (!extension_loaded('zip')) {
            throw new \Exception('Bu işlemi yapabilmek için ZipArchive extensionu yüklü olmalı!');
        }

        if ($config)
            $this->config['folder'] = $config;

        $files = $this->getDirectory($this->config['folder']['dir']);

        $zip = new ZipArchive();
        $zip->open($this->config['folder']['file'], ZipArchive::CREATE);
        foreach ($files as $file) {
            $zip->addFile($file);
        }
        if (isset($this->config['db']['file'])) {
            $zip->addFile($this->config['db']['file'], basename($this->config['db']['file']));
        }
        $zip->close();

        /**
         * Eğer arşivleme başarılıysa sql dosyasını kaldır
         */
        $result = file_exists($this->config['folder']['file']);
        if ($result) {
            if (isset($this->config['db']['file'])) {
                @unlink($this->config['db']['file']);
            }
        }

        return $result;

    }

    /**
     * @return bool
     * @throws Exception
     */
    public function full()
    {
        if ($this->mysql()){
            if ($this->folder()){
                return true;
            } else {
                throw new \Exception('Zipleme işlemi sırasında bir hata oluştu!');
            }
        } else {
            throw new \Exception('Mysqldump sırasında bir hata oluştu!');
        }
    }

}
