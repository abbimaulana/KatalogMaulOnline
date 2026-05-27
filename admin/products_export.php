<?php
$products = get_all_products();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="products-' . date('Ymd') . '.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, ['product_code', 'name', 'description', 'price', 'is_direct_payment', 'is_active']);
foreach ($products as $product) {
    fputcsv($output, [
        $product['product_code'],
        $product['name'],
        $product['description'],
        $product['price'],
        $product['is_direct_payment'],
        $product['is_active'],
    ]);
}

fclose($output);
exit;
