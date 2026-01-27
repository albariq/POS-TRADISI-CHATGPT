<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_outlets')) {
            Schema::create('product_outlets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['product_id', 'outlet_id']);
                $table->index(['outlet_id']);
            });
        }

        if (Schema::hasColumn('products', 'outlet_id')) {
            $driver = DB::connection()->getDriverName();
            $insertKeyword = $driver === 'sqlite' ? 'INSERT OR IGNORE' : 'INSERT IGNORE';
            DB::statement("
                {$insertKeyword} INTO product_outlets (product_id, outlet_id, created_at, updated_at)
                SELECT id, outlet_id, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                FROM products
            ");

            Schema::table('products', function (Blueprint $table) {
                // Drop index constraints safely in separate steps.
                $table->dropIndex(['outlet_id', 'name']);
            });

            $this->dropForeignKeyIfExists('products', 'outlet_id');
            $this->dropUniqueIndexIfExists('products', 'outlet_id', 'sku');

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('outlet_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('products', 'outlet_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
                $table->unique(['outlet_id', 'sku']);
                $table->index(['outlet_id', 'name']);
            });
        }

        if (Schema::hasColumn('products', 'outlet_id')) {
            DB::statement('
                UPDATE products
                SET outlet_id = (
                    SELECT outlet_id
                    FROM product_outlets
                    WHERE product_outlets.product_id = products.id
                    ORDER BY product_outlets.id ASC
                    LIMIT 1
                )
            ');
        }

        Schema::dropIfExists('product_outlets');
    }

    private function dropForeignKeyIfExists(string $table, string $column): void
    {
        $connection = DB::connection();
        if ($connection->getDriverName() !== 'mysql') {
            // For other drivers, try the default drop.
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->dropForeign([$column]);
            });

            return;
        }

        $database = $connection->getDatabaseName();
        $results = DB::select('
            SELECT CONSTRAINT_NAME as name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ', [$database, $table, $column]);

        foreach ($results as $row) {
            $constraint = $row->name;
            if ($constraint) {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`");
            }
        }
    }

    private function dropUniqueIndexIfExists(string $table, string ...$columns): void
    {
        $connection = DB::connection();
        if ($connection->getDriverName() !== 'mysql') {
            Schema::table($table, function (Blueprint $table) use ($columns) {
                $table->dropUnique($columns);
            });

            return;
        }

        $database = $connection->getDatabaseName();
        $columnList = implode("','", $columns);
        $results = DB::select("
            SELECT s.INDEX_NAME as name
            FROM information_schema.STATISTICS s
            WHERE s.TABLE_SCHEMA = ?
              AND s.TABLE_NAME = ?
              AND s.NON_UNIQUE = 0
              AND s.COLUMN_NAME IN ('{$columnList}')
            GROUP BY s.INDEX_NAME
            HAVING COUNT(*) = ?
        ", [$database, $table, count($columns)]);

        foreach ($results as $row) {
            $indexName = $row->name;
            if ($indexName) {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
            }
        }
    }
};
