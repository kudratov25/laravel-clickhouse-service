<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClickHouseExportService
{
    protected $batchSize;
    protected $model;
    protected $columns;
    protected $tableName;

    // Modify constructor to accept class-string for model
    public function __construct(string $modelClass, array $columns, $tableName = 'trips', $batchSize = 1000)
    {
        $this->batchSize = $batchSize;
        $this->model = $modelClass;
        $this->columns = $columns;
        $this->tableName = $tableName;
    }

    /**
     * Export data from MySQL/PostgreSQL to ClickHouse
     */
    public function exportData()
    {
        try {
            $model = $this->model;

            $model::chunk($this->batchSize, function ($data) {
                $this->exportToClickHouse($data);
            });

            return ['status' => 'success', 'message' => 'Data exported successfully!'];
        } catch (\Exception $e) {
            \Log::error("Error during export: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }



    /**
     * Export a batch of data to ClickHouse
     *
     * @param \Illuminate\Support\Collection $data
     */
    protected function exportToClickHouse($data)
    {
        \Log::info("Processing chunk with " . count($data) . " records");

        $rowsToInsert = [];

        foreach ($data as $row) {
            $rowsToInsert[] = $this->transformRow($row);
        }

        try {
            $tableName = 'users';
            $columns = $this->columns;

            // Generate VALUES part of the query
            $values = [];
            foreach ($rowsToInsert as $row) {
                $rowValues = array_map(function ($value) {
                    // Ensure proper escaping for strings
                    return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                }, $row);
                $values[] = '(' . implode(',', $rowValues) . ')';
            }

            $insertQuery = "INSERT INTO {$tableName} (" . implode(',', $columns) . ") VALUES " . implode(',', $values);

            DB::connection('clickhouse')->statement($insertQuery);

            \Log::info("Exported " . count($rowsToInsert) . " records to ClickHouse table {$tableName}.");
        } catch (\Exception $e) {
            \Log::error("Error exporting data to ClickHouse: " . $e->getMessage());

            // Save the error to MySQL
            $this->saveErrorToMySQL($rowsToInsert, $e->getMessage(), $tableName);
        }
    }




    /**
     * Transform the data to match the ClickHouse schema
     *
     * @param $row
     * @return array
     */
    protected function transformRow($row)
    {
        $transformedRow = [];

        foreach ($this->columns as $column) {
            $transformedRow[$column] = $row->{$column};
        }

        return $transformedRow;
    }

    // errors table
    protected function saveErrorToMySQL($rows, $errorMessage, $tableName)
    {
        try {
            // Ensure the error table exists
            $this->ensureErrorTableExists();

            // Insert each failed row into the error table
            foreach ($rows as $row) {
                DB::table('data_error_table')->insert([
                    'table_name' => $tableName,
                    'data' => json_encode($row),
                    'error_message' => $errorMessage,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            \Log::info("Saved " . count($rows) . " failed records to the error table.");
        } catch (\Exception $e) {
            \Log::error("Failed to save error details to MySQL: " . $e->getMessage());
        }
    }

    protected function ensureErrorTableExists()
    {
        if (!Schema::hasTable('data_error_table')) {
            Schema::create('data_error_table', function ($table) {
                $table->bigIncrements('id');
                $table->string('table_name'); // Original table name
                $table->json('data');        // Failed data in JSON format
                $table->text('error_message'); // Error message
                $table->timestamps();        // created_at and updated_at
            });

            \Log::info("Created 'data_error_table' in the database.");
        }
    }
}
