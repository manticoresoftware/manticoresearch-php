<?php

require_once __DIR__ . '/vendor/autoload.php';
use Manticoresearch\{Index, Client, Search};


$manticore = new Manticoresearch\Client(['host'=>'127.0.0.1','port'=>9308]);

$manticore->tables()->drop(
	[
		'table' => 'products1',
		'body' => ['silent' => true],
	]
);


$productIndex = $manticore->table('products1');
$data = json_decode(file_get_contents(dirname(__DIR__) . '/manticoresearch-php/manticore.json'), true);

//print_r($productIndex->status());
//print_r($productIndex->describe());

$createTable = true;

if ($createTable) {
	$productIndex->create([
		'id' => ['type' => 'int'],
		'is_allowed' => ['type' => 'bool'],
		'is_active' => ['type' => 'bool'],
		'is_new' => ['type' => 'bool'],
		'is_sale' => ['type' => 'bool'],
		'is_popular' => ['type' => 'bool'],
		'is_premium' => ['type' => 'bool'],
		'art' => ['type' => 'text'],
		'supplier_art' => ['type' => 'text'],
		'name' => ['type' => 'text'],
		'slug' => ['type' => 'text'],
		'brand_id' => ['type' => 'int'],
		'type_id' => ['type' => 'int'],
		'category_single_ids' => ['type' => 'multi'],
		'category_ids' => ['type' => 'multi'],
		'type_ids' => ['type' => 'multi'],
		'collection_ids' => ['type' => 'multi'],
		'season_ids' => ['type' => 'multi'],
		'color_ids' => ['type' => 'multi'],
		'material_ids' => ['type' => 'multi'],
		'lining_ids' => ['type' => 'multi'],
		'supplier_season_ids' => ['type' => 'multi'],
		'insole_material_ids' => ['type' => 'multi'],
		'outsole_material_ids' => ['type' => 'multi'],
		'sole_attaching_method_ids' => ['type' => 'multi'],
		'style_ids' => ['type' => 'multi'],
		'heel_ids' => ['type' => 'multi'],
		'fastener_type_ids' => ['type' => 'multi'],
		'shoe_width_ids' => ['type' => 'multi'],
		'offers' => ['type' => 'json'],
		'offers_names_ids' => ['type' => 'multi'],
		'offers_names' => ['type' => 'json'],
		'offers_count' => ['type' => 'int'],
		'purchase_price' => ['type' => 'float'],
		'selling_price' => ['type' => 'float'],
		'price' => ['type' => 'float'],
		'measurement_size' => ['type' => 'float'],
		'heel_height' => ['type' => 'float'],
		'shaft_height' => ['type' => 'float'],
		'shaft_girth' => ['type' => 'float'],
		'additional_features' => ['type' => 'text'],
		'text' => ['type' => 'text'],
		'order_index' => ['type' => 'int'],
		'order_partition_key' => ['type' => 'int'],
		'meta_title' => ['type' => 'text'],
		'meta_description' => ['type' => 'text'],
		'meta_keywords' => ['type' => 'text'],
		'meta_image_alt' => ['type' => 'text'],
		'meta_image_title' => ['type' => 'text'],
		'meta_text1' => ['type' => 'text'],
		'meta_text2' => ['type' => 'text'],
		'photos' => ['type' => 'json'],
		'gallery_photos' => ['type' => 'json'],
		'similar_product_ids' => ['type' => 'multi'],
		'group_by_photos_product_ids' => ['type' => 'multi'],
		'group_by_specs_product_ids' => ['type' => 'multi'],
		'relations_data' => ['type' => 'json'],
		'created_at' => ['type' => 'timestamp'],
		'updated_at' => ['type' => 'timestamp'],
		'idx_updated_at' => ['type' => 'timestamp'],
		'idx_order_index_updated_at' => ['type' => 'timestamp'],
	],
		[
			'rt_mem_limit' => '512M',
			'min_infix_len' => 2
		]
		);
}

foreach ($data as $productId => $productData) {
	//echo $productId . PHP_EOL;
	$productIndex->addDocument($productData, $productId);
}

foreach ($data as $productId => $productData) {
	//echo $productId . PHP_EOL;
	$productIndex->updateDocument($productData, $productId);
}

echo 'OK';