<?php
session_start();
include('inc/header.php');
include('inc/auth.php');
include('class/finance.php');

requireAuth();

$finance = new Finance();
$ukms = $finance->getUkm();

// --- UKM Selection Logic --- //
$selected_ukm_id = null; // Represents the currently selected UKM ID (null for All UKMs)
$ukm_name = 'All UKMs'; // Default display name

// Determine selected UKM ID from GET, then SESSION
// The value '0' from the dropdown will indicate 'All UKMs'

$selected_ukm_id_from_param = isset($_GET['ukm_id']) ? $_GET['ukm_id'] : null;
$selected_ukm_id_from_session = isset($_SESSION['ukm_id']) ? $_SESSION['ukm_id'] : null;

$current_selected_ukm_id = null;

if ($selected_ukm_id_from_param !== null) {
    $current_selected_ukm_id = $selected_ukm_id_from_param;
} else if ($selected_ukm_id_from_session !== null) {
    $current_selected_ukm_id = $selected_ukm_id_from_session;
} else if (isPreviewMode() && count($ukms) > 0) {
    // If in preview mode and no ukm_id is set, use the first UKM as the default selection displayed
    // However, conversion should still default to All UKMs unless a specific UKM is selected.
    // Let's keep the default conversion to All UKMs unless explicitly selected.
    // For display purposes, if no selection is made, show the first UKM info if in preview mode.
    // This might need clarification based on desired behavior.
    // For now, we'll stick to null = All UKMs.
}

if ($current_selected_ukm_id !== null && $current_selected_ukm_id !== '0' && $current_selected_ukm_id !== '') {
    // A specific UKM ID is selected
    $found_ukm = false;
    foreach ($ukms as $ukm) {
        if ((string)$ukm['id'] === (string)$current_selected_ukm_id) {
            $ukm_id = $ukm['id']; // Set ukm_id to the actual ID
            $ukm_name = $ukm['nama_ukm'];
            $found_ukm = true;
            break;
        }
    }
    // If selected UKM ID is not valid, revert to All UKMs
    if (!$found_ukm) {
        $ukm_id = null;
        $ukm_name = 'All UKMs';
        // Optionally set an error message
        $message = 'Selected UKM not found. Displaying and converting data for all UKMs.';
        $message_type = 'warning';
    }
} else {
    // 'All UKMs' is selected or no selection made
    $ukm_id = null;
    $ukm_name = 'All UKMs';
}

// Store selected UKM ID in session (store null for All UKMs)
$_SESSION['ukm_id'] = $ukm_id;


// --- XML Conversion Logic --- //
$xml_generated = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['convert_to_xml'])) {
    // Get the ukm_id to be used for conversion from the hidden input in the form
    $ukm_id_for_conversion = isset($_POST['ukm_id_to_convert']) && $_POST['ukm_id_to_convert'] !== '' ? $_POST['ukm_id_to_convert'] : null;
     if ($ukm_id_for_conversion === '0') $ukm_id_for_conversion = null; // Handle '0' from dropdown

    try {
        // Pass selected UKM ID (or null for all) to getXml
        $finance->getXml($ukm_id_for_conversion);
        $xml_generated = true;
        // Determine the name for the success message based on the ID used for conversion
        $conversion_ukm_name = 'All UKMs';
        if ($ukm_id_for_conversion !== null) {
             foreach ($ukms as $ukm) {
                if ((string)$ukm['id'] === (string)$ukm_id_for_conversion) {
                    $conversion_ukm_name = $ukm['nama_ukm'];
                    break;
                }
            }
        }
        $message = 'Transaksi data successfully exported to transaksi.xml for ' . $conversion_ukm_name . '.';
        $message_type = 'success';

         // After conversion, re-read the XML to display the newly generated data
         // This will happen in the XML reading logic below.

    } catch (Exception $e) {
        $message = 'Error exporting data: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// --- Read and Parse XML Logic --- //
$xml_data = [];
$xml_file_path = 'api/transaksi.xml'; // Assuming it's saved in the root
$xml_file_exists = file_exists($xml_file_path);

if ($xml_file_exists) {
    $xml = simplexml_load_file($xml_file_path);
    if ($xml) {
        // Filter displayed data based on $ukm_id_for_display (null for All UKMs)
        foreach ($xml->transaksi as $transaksi) {
            if ($ukm_id === null || (string)$transaksi->ukm_id === (string)$ukm_id) {
                 $xml_data[] = [
                    'id' => (string)$transaksi->id,
                    'ukm_id' => (string)$transaksi->ukm_id,
                    'nama_ukm' => (string)$transaksi->nama_ukm,
                    'jenis' => (string)$transaksi->jenis,
                    'kategori' => (string)$transaksi->kategori,
                    'jumlah' => (float)$transaksi->jumlah,
                    'tanggal' => (string)$transaksi->tanggal,
                    'keterangan' => (string)$transaksi->keterangan,
                ];
            }
        }
         // Message if file exists but no data for the currently selected display UKM
         if (empty($xml_data) && $ukm_id !== null) {
             $message = 'No transaction data found in transaksi.xml for the selected UKM.';
             $message_type = 'info';
         } else if (empty($xml_data) && $ukm_id === null && $xml_file_exists) {
              $message = 'transaksi.xml exists but contains no transaction data.';
              $message_type = 'info';
         }

    } else {
         $message = 'Error parsing transaksi.xml file. It might be corrupt or not generated yet.';
         $message_type = 'danger';
    }
} else {
     // Message if the XML file does not exist
     $message = 'transaksi.xml file not found. Please generate it first by clicking the convert button.';
     $message_type = 'info';
}

?>

<div class="wrapper">
    <?php include('inc/sidebar.php'); ?>
    <div id="main-content">
        <div class="content-toggle">
            <button id="content-toggle-btn">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
        <section class="convert-page">
            <div class="page-header">
                <h1>Konversi Data Transaksi ke XML</h1>
                <?php include('inc/profile_bar.php'); ?>
            </div>

            <div class="ukm-selector">
                <form method="GET" action="">
                    <label for="ukm">Pilih UKM untuk Ditampilkan:</label>
                    <select name="ukm_id" id="ukm" onchange="this.form.submit()">
                        <option value="0" <?php echo ($ukm_id === null) ? 'selected' : ''; ?>>All UKMs</option>
                        <?php foreach($ukms as $ukm): ?>
                            <option value="<?php echo $ukm['id']; ?>" <?php echo ((string)$ukm['id'] === (string)$ukm_id) ? 'selected' : ''; ?>>
                                <?php echo $ukm['nama_ukm']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            
            <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="convert-section">
                <form method="POST" action="">
                    <input type="hidden" name="ukm_id_to_convert" value="<?php echo htmlspecialchars($ukm_id === null ? '0' : $ukm_id); ?>">
                    <button type="submit" name="convert_to_xml" class="btn btn-primary">Convert Data to XML for <?php echo $ukm_name; ?></button>
                </form>
            </div>

            <div class="xml-data-table">
                <h2>Data dari transaksi.xml (<?php echo $ukm_name; ?>)</h2>
                <?php if (empty($xml_data) && $xml_file_exists && $ukm_id !== null): ?>
                     <div class="empty-state">
                        <i class="fas fa-file-code"></i>
                        <p>No transaction data found in transaksi.xml for the selected UKM.</p>
                    </div>
                <?php elseif (empty($xml_data) && !$xml_file_exists): ?>
                     <div class="empty-state">
                        <i class="fas fa-file-code"></i>
                        <p>transaksi.xml not found. Please generate it first by clicking the convert button.</p>
                    </div>
                <?php elseif (empty($xml_data) && $xml_file_exists && $ukm_id === null): ?>
                      <div class="empty-state">
                         <i class="fas fa-file-code"></i>
                         <p>No transaction data found in transaksi.xml.</p>
                      </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>UKM ID</th>
                                <th>Nama UKM</th>
                                <th>Jenis</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($xml_data as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ukm_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_ukm']); ?></td>
                                    <td><?php echo htmlspecialchars($row['jenis']); ?></td>
                                    <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                                    <td><?php echo number_format($row['jumlah'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                                    <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<style>
.convert-page {
    padding: 40px 20px; /* Increased top padding */
}

.ukm-selector {
    margin-bottom: 20px; /* Space below the UKM selector */
}

.convert-section,
.xml-data-table {
    margin-bottom: 20px; /* Add space between sections */
}

.xml-data-table table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.xml-data-table th,
.xml-data-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

.xml-data-table th {
    background-color: #f2f2f2;
}
</style>

<script>
// Content toggle button functionality (assuming this is consistent)
document.addEventListener('DOMContentLoaded', function() {
    const contentToggleBtn = document.getElementById('content-toggle-btn');
    if (contentToggleBtn) {
        contentToggleBtn.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('main-content').classList.toggle('expanded');
            
            // Save sidebar state to localStorage
            if (document.getElementById('sidebar').classList.contains('collapsed')) {
                localStorage.setItem('sidebar', 'collapsed');
            } else {
                localStorage.setItem('sidebar', 'expanded');
            }
        });
    }

    // Mobile detection on page load (assuming this is consistent)
    function checkMobile() {
        if (window.innerWidth <= 768) {
            document.getElementById('sidebar').classList.add('collapsed');
            document.getElementById('main-content').classList.add('expanded');
        } else if (localStorage.getItem('sidebar') !== 'collapsed') {
            document.getElementById('sidebar').classList.remove('collapsed');
            document.getElementById('main-content').classList.remove('expanded');
        }
    }

    // Check on page load and resize
    checkMobile();
    window.addEventListener('resize', checkMobile);
});
</script> 