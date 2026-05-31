<?php
require __DIR__ . '/application/vendor/autoload.php';
$app = require_once __DIR__ . '/application/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "<pre>NexCore Address Module — Table Setup\n";
echo str_repeat('=', 50) . "\n\n";

try {
    // ─── TABLE 1: nx_address_types ───
    if (!Schema::hasTable('nx_address_types')) {
        Schema::create('nx_address_types', function ($table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
        echo "✅ Created table: nx_address_types\n";

        $types = [
            ['name' => 'Residential',           'code' => 'RES',  'description' => 'Primary home or residential address where an individual resides permanently.'],
            ['name' => 'Postal',                 'code' => 'POST', 'description' => 'Post office box or postal delivery address used for receiving mail and correspondence.'],
            ['name' => 'Business',               'code' => 'BUS',  'description' => 'Registered business or commercial address for a company or organisation.'],
            ['name' => 'Farm',                   'code' => 'FARM', 'description' => 'Rural farm or agricultural holding address, typically without standard street numbering.'],
            ['name' => 'Informal Settlement',    'code' => 'INF',  'description' => 'Address within an informal settlement or unplanned housing area.'],
            ['name' => 'Traditional Authority',  'code' => 'TRAD', 'description' => 'Address under a traditional authority or tribal area, common in rural South Africa.'],
            ['name' => 'Workplace',              'code' => 'WORK', 'description' => 'Employment or workplace address where an individual is based for work purposes.'],
            ['name' => 'Temporary',              'code' => 'TEMP', 'description' => 'Short-term or temporary address, such as a hostel, shelter, or transitional housing.'],
            ['name' => 'Delivery',               'code' => 'DEL',  'description' => 'Address designated for courier and package deliveries, may differ from residential address.'],
            ['name' => 'Private Bag',            'code' => 'PBAG', 'description' => 'Private bag address allocated by the South African Post Office for bulk mail recipients.'],
        ];

        $now = now();
        foreach ($types as $type) {
            DB::table('nx_address_types')->insert(array_merge($type, [
                'is_active'  => true,
                'is_deleted' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
        echo "   ↳ Seeded " . count($types) . " address types\n";
    } else {
        echo "⏭️  Table nx_address_types already exists\n";
    }

    // ─── TABLE 2: nx_addresses (main address records) ───
    if (!Schema::hasTable('nx_addresses')) {
        Schema::create('nx_addresses', function ($table) {
            $table->id();
            $table->string('unit_number', 50)->nullable();
            $table->string('complex_name', 200)->nullable();
            $table->string('street_number', 50)->nullable();
            $table->string('street_name', 255)->nullable();
            $table->unsignedBigInteger('suburb_id')->nullable();
            $table->string('city', 200)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('municipality_id')->nullable();
            $table->unsignedBigInteger('ward_id')->nullable();
            $table->string('country', 10)->default('ZA');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('google_formatted_address')->nullable();
            $table->string('address_category', 50)->default('Commercial');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('suburb_id');
            $table->index('province_id');
            $table->index('city');
            $table->index('postal_code');
        });
        echo "✅ Created table: nx_addresses\n";
    } else {
        echo "⏭️  Table nx_addresses already exists\n";
    }

    // ─── TABLE 3: nx_address_details (extended / secondary) ───
    if (!Schema::hasTable('nx_address_details')) {
        Schema::create('nx_address_details', function ($table) {
            $table->id();
            $table->unsignedBigInteger('address_id');
            // Property
            $table->string('floor_level', 50)->nullable();
            $table->string('building_name', 200)->nullable();
            $table->string('estate_name', 200)->nullable();
            $table->string('section_number', 50)->nullable();
            // Farm / Rural
            $table->string('farm_name', 200)->nullable();
            $table->string('farm_number', 50)->nullable();
            $table->string('stand_number', 50)->nullable();
            // Government / Compliance
            $table->string('erf_number', 50)->nullable();
            $table->string('sg_code', 50)->nullable();
            $table->string('municipal_account_number', 100)->nullable();
            // Digital Addressing
            $table->string('plus_code', 50)->nullable();
            $table->string('what3words', 100)->nullable();
            $table->string('google_place_id', 255)->nullable();
            $table->text('map_url')->nullable();
            $table->string('address_source', 50)->default('Manual');
            // Verification
            $table->boolean('is_verified')->default(false);
            $table->date('verified_date')->nullable();
            $table->timestamps();

            $table->foreign('address_id')->references('id')->on('nx_addresses')->onDelete('cascade');
            $table->index('address_id');
        });
        echo "✅ Created table: nx_address_details\n";
    } else {
        echo "⏭️  Table nx_address_details already exists\n";
    }

    // ─── TABLE 4: nx_address_links (polymorphic linking) ───
    if (!Schema::hasTable('nx_address_links')) {
        Schema::create('nx_address_links', function ($table) {
            $table->id();
            $table->unsignedBigInteger('address_id');
            $table->string('linkable_type', 100);
            $table->unsignedBigInteger('linkable_id');
            $table->unsignedBigInteger('address_type_id')->nullable();
            $table->string('address_label', 100)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('address_id')->references('id')->on('nx_addresses')->onDelete('cascade');
            $table->index(['linkable_type', 'linkable_id']);
            $table->index('address_type_id');
        });
        echo "✅ Created table: nx_address_links\n";
    } else {
        echo "⏭️  Table nx_address_links already exists\n";
    }

    echo "\n" . str_repeat('=', 50) . "\n";
    echo "✅ NexCore Address tables setup complete!\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "</pre>";
@unlink(__FILE__);
