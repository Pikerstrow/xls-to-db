<?php


namespace App\Services\Excel;

use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;
use Throwable;


class ProductsImport implements WithChunkReading, WithStartRow, OnEachRow
{
    use Importable;

    private $stock_statuses;
    private $offset_rows;
    private $failed_rows_counter;
    private $success_rows_counter;
    private $errors_log_path;

    public function __construct()
    {
        $this->stock_statuses       = config('statuses.stock');
        $this->offset_rows          = false;
        $this->failed_rows_counter  = 0;
        $this->success_rows_counter = 0;
        $this->errors_log_path      = storage_path('app/imports/errors.txt');
    }

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $row_number = $row->getIndex();
        $row = $row->toArray();
        $this->offset_rows = !empty($row[10]) ? true : false;
        $validator = Validator::make(
            $row,
            $this->getValidationRules(),
            $this->getValidationMessages(),
            $this->getCustomAttributes()
        );

        $product_name = trim($this->offset_rows ? $row[5] : $row[4]);
        if ($validator->fails()) {
            $this->handleValidationError($row_number, $product_name, $validator);
        } else {
            $product = new Product(
                [
                    'category_id' => $this->makeCategoriesTree($row),
                    'manufacturer_id' => Manufacturer::firstOrCreate(['name' => $this->offset_rows ? $row[4] : $row[3]])->id,
                    'name' => $product_name,
                    'description' => $this->offset_rows ? $row[7] : $row[6],
                    'vendor_code' => trim($this->offset_rows ? $row[6] : $row[5]),
                    'price' => $this->offset_rows ? $row[8] : $row[7],
                    'warranty' => is_string($this->offset_rows ? $row[9] : $row[8]) ? 0 : ($this->offset_rows ? $row[9] : $row[8]),
                    'stock' => $this->stock_statuses[$this->offset_rows ? $row[10] : $row[9]]
                ]
            );
            $product->save(); //Can be replaced with static method updateOrCreate if there is necessity to update existing products.
            $this->success_rows_counter++;
        }
    }

    /**
     * @param int $row_index
     * @param string $product_name
     * @param $validator
     */
    private function handleValidationError(int $row_index, string $product_name, $validator): void
    {
        try {
            $error_messages = [];
            foreach ($validator->errors()->messages() as $messages) {
                foreach ($messages as $message){
                    $error_messages[] = $message;
                }
            }

            $reason  = "Рядок №{$row_index}. Товар '{$product_name}'. ";
            $reason .= count($error_messages) > 1 ? 'Причини: ' : 'Причина: ';
            $reason .= implode(', ', $error_messages) . "\n";

            $handler = fopen($this->errors_log_path, 'a');
            fwrite($handler, $reason);
            fclose($handler);
            $this->failed_rows_counter++;
        } catch (Throwable $exception) {
            Log::stack(['excel'])->info('Handling validation error failed. Reason: ' . $exception->getMessage());
        }
    }

    /**
     * @return array
     */
    private function getValidationRules(): array
    {
        return $this->offset_rows
            ? [
                0 => 'nullable',
                1 => 'nullable|string|max:255',
                2 => 'nullable|string|max:255',
                3 => 'nullable|string|max:255',
                4 => 'string|max:255',
                5 => 'required|string|max:255|unique:products,name',
                6 => 'required|unique:products,vendor_code',
                7 => 'string',
                8 => 'numeric',
                9 => 'required',
                10 => 'nullable|string|max:255',
            ]
            : [
                0 => 'nullable|string|max:255',
                1 => 'nullable|string|max:255',
                2 => 'nullable|string|max:255',
                3 => 'string|max:255',
                4 => 'required|string|max:255|unique:products,name',
                5 => 'required|unique:products,vendor_code',
                6 => 'string',
                7 => 'numeric',
                8 => 'required',
                9 => 'nullable|string|max:255',
                10 => 'nullable'
            ];
    }

    /**
     * @return array
     */
    private function getCustomAttributes(): array
    {
        return $this->offset_rows
            ? [
                '1' => 'Рубрика',
                '2' => 'Рубрика',
                '3' => 'Категорія',
                '4' => 'Виробник',
                '5' => 'Назва',
                '6' => 'Артикул',
                '7' => 'Опис',
                '8' => 'Ціна',
                '9' => 'Гарантія',
                '10' => 'Наявність',
            ]
            : [
                '0' => 'Рубрика',
                '1' => 'Рубрика',
                '2' => 'Категорія',
                '3' => 'Виробник',
                '4' => 'Назва',
                '5' => 'Артикул',
                '6' => 'Опис',
                '7' => 'Ціна',
                '8' => 'Гарантія',
                '9' => 'Наявність',
            ];
    }

    /**
     * @return array
     */
    private function getValidationMessages(): array
    {
        return [
            'string' => "Згачення поля :attribute повинне бути строкою",
            'max' => "Максимальна довжина значення поля :attribute становить :max символів",
            'required' => "'Поле :attribute є обо\'язковою'",
            'unique' => "Товар із вказаним значенням поля :attribute вже додано до БД",
            'numeric' => "Значення поля :attribute повинне бути числовим значенням",
        ];
    }

    /**
     * @param array $row
     * @return mixed
     */
    private function makeCategoriesTree(array $row)
    {
        return Category::firstOrCreate(
            ['name' => $this->offset_rows ? $row[3] : $row[2]],
            [
                'parent_id' => !empty($this->offset_rows ? $row[2] : $row[1]) ? Category::firstOrCreate(
                    ['name' => $this->offset_rows ? $row[2] : $row[1]],
                    ['parent_id' => !empty($this->offset_rows ? $row[1] : $row[0]) ? Category::firstOrCreate(['name' => $this->offset_rows ? $row[1] : $row[0]])->id : null]
                )->id : null
            ]
        )->id;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 500;
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * @param $file
     * @return \Generator
     */
    public static function getImportErrors($file): \Generator
    {
        $f = fopen($file, 'r');
        try {
            while ($line = fgets($f)) {
                yield $line;
            }
        } finally {
            fclose($f);
            unlink($file);
        }
    }

    /**
     * @return int
     */
    public function getFailedRowsNumber(): int
    {
        return $this->failed_rows_counter;
    }

    /**
     * @return int
     */
    public function getSuccessRowsNumber(): int
    {
        return $this->success_rows_counter;
    }
}
