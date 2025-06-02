<?php

include('class/finance.php');

$finance = new Finance();

try {
    // The getXml method in Finance class already handles fetching all transactions
    // and writing them to transaksi.xml
    $finance->getXml();
    echo "Transaksi data successfully exported to transaksi.xml";
} catch (Exception $e) {
    echo "Error exporting data: " . $e->getMessage();
}

?>