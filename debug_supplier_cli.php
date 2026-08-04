<?php
// Direct DB test - no framework needed
try {
    $db = new PDO('mysql:host=localhost;dbname=alfarezmart;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected OK\n";

    // 1. Check suppliers table columns
    $stmt = $db->query('DESCRIBE suppliers');
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Suppliers columns: " . implode(', ', $cols) . "\n";
    
    // 2. Find products that have purchase history
    $stmt2 = $db->query('SELECT p.id, p.name, pu.supplier_id, s.name as sname 
        FROM products p 
        JOIN purchase_items pi ON pi.product_id = p.id 
        JOIN purchases pu ON pi.purchase_id = pu.id 
        LEFT JOIN suppliers s ON s.id = pu.supplier_id
        GROUP BY p.id, p.name, pu.supplier_id, s.name
        ORDER BY p.id LIMIT 10');
    $prods = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    echo "\nProducts with purchase history:\n";
    foreach ($prods as $p) {
        echo "  Product ID={$p['id']} Name={$p['name']} SupplierID={$p['supplier_id']} SupplierName={$p['sname']}\n";
    }
    
    if (!empty($prods)) {
        $testPid = $prods[0]['id'];
        echo "\n--- Testing enrichProductDetailData supplier query for product_id=$testPid ---\n";
        
        // Step A: Get supplier IDs from purchases
        $stmt = $db->prepare("SELECT DISTINCT pu.supplier_id FROM purchase_items pi JOIN purchases pu ON pi.purchase_id = pu.id WHERE pi.product_id = :pid AND pu.supplier_id IS NOT NULL AND pu.supplier_id > 0");
        $stmt->execute([':pid' => $testPid]);
        $supplierIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Supplier IDs from purchases: " . json_encode($supplierIds) . "\n";
        
        // Step B: Get supplier info WITH phone
        foreach ($supplierIds as $sid) {
            try {
                $stmt = $db->prepare("SELECT id as supplier_id, name as supplier_name, address, notes, phone FROM suppliers WHERE id = :sid");
                $stmt->execute([':sid' => $sid]);
                $sup = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "Supplier info WITH phone for sid=$sid: " . json_encode($sup) . "\n";
            } catch (Throwable $e) {
                echo "ERROR fetching supplier WITH phone for sid=$sid: " . $e->getMessage() . "\n";
                
                // Try WITHOUT phone
                try {
                    $stmt = $db->prepare("SELECT id as supplier_id, name as supplier_name, address, notes FROM suppliers WHERE id = :sid");
                    $stmt->execute([':sid' => $sid]);
                    $sup = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo "Supplier info WITHOUT phone for sid=$sid: " . json_encode($sup) . "\n";
                } catch (Throwable $e2) {
                    echo "ERROR fetching supplier WITHOUT phone for sid=$sid: " . $e2->getMessage() . "\n";
                }
            }
        }
    }

} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
