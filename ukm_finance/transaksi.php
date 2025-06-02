<?php
session_start();
header("Access-Control-Allow-Origin: *");
include('inc/header.php');
include('inc/auth.php');
include('class/finance.php');

// Check if user is authenticated (either logged in or in preview mode)
requireAuth();

$finance = new Finance();
$ukms = $finance->getUkm();
$ukm_id = isset($_GET['ukm_id']) ? $_GET['ukm_id'] : (isset($_SESSION['ukm_id']) ? $_SESSION['ukm_id'] : (count($ukms) > 0 ? $ukms[0]['id'] : null));

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $imageValue = '';
    
    // Handle image upload or URL
    if (!empty($_FILES['image_upload']['name'])) {
        // File upload
        $uploadResult = $finance->handleImageUpload($_FILES['image_upload']);
        if ($uploadResult['status'] === 1) {
            $imageValue = $uploadResult['filepath'];
        } else {
            $error_message = $uploadResult['message'];
        }
    } elseif (!empty($_POST['image_url'])) {
        // Image URL
        if ($finance->validateImageUrl($_POST['image_url'])) {
            $imageValue = $_POST['image_url'];
        } else {
            $error_message = "Invalid image URL provided";
        }
    }
    
    if (!isset($error_message)) {
        if ($_POST['action'] === 'add') {
            $result = $finance->tambahTransaksi([
                'ukm_id' => $_POST['ukm_id'],
                'jenis' => $_POST['jenis'],
                'kategori' => $_POST['kategori'],
                'jumlah' => $_POST['jumlah'],
                'tanggal' => $_POST['tanggal'],
                'keterangan' => $_POST['keterangan'],
                'image' => $imageValue
            ]);
            
            if ($result['status'] === 1) {
                header("Location: transaksi.php?ukm_id=" . $_POST['ukm_id'] . "&success=1");
                exit();
            } else {
                $error_message = $result['status_pesan'];
            }
        } elseif ($_POST['action'] === 'edit') {
            $result = $finance->updateTransaksi($_POST['id'], [
                'ukm_id' => $_POST['ukm_id'],
                'jenis' => $_POST['jenis'],
                'kategori' => $_POST['kategori'],
                'jumlah' => $_POST['jumlah'],
                'tanggal' => $_POST['tanggal'],
                'keterangan' => $_POST['keterangan'],
                'image' => $imageValue
            ]);
            
            if ($result['status'] === 1) {
                header("Location: transaksi.php?ukm_id=" . $_POST['ukm_id'] . "&success=3");
                exit();
            } else {
                $error_message = $result['status_pesan'];
            }
        }
    }
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $result = $finance->hapusTransaksi($_POST['id']);
    if ($result['status'] === 1) {
        header("Location: transaksi.php?ukm_id=" . $_POST['ukm_id'] . "&success=2");
        exit();
    } else {
        $error_message = $result['status_pesan'];
    }
}

// If in preview mode, use the first UKM
if (isPreviewMode() && !$ukm_id && count($ukms) > 0) {
    $ukm_id = $ukms[0]['id'];
}

// Store selected UKM in session
if($ukm_id) {
    $_SESSION['ukm_id'] = $ukm_id;
}

// Get transactions for the selected UKM
$transaksi = $finance->getTransaksi($ukm_id);

// Get transaction to edit if edit_id is provided
$edit_transaksi = null;
if (isset($_GET['edit_id'])) {
    $edit_transaksi = $finance->getTransaksiById($_GET['edit_id']);
}

// Get UKM name
$ukm_name = "";
foreach($ukms as $ukm) {
    if($ukm['id'] == $ukm_id) {
        $ukm_name = $ukm['nama_ukm'];
        break;
    }
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
        <section class="transaksi-page">
            <div class="page-header">
                <h1>Manajemen Transaksi <?php echo $ukm_name ? "- $ukm_name" : ""; ?></h1>
                <?php include('inc/profile_bar.php'); ?>
            </div>
    
    <?php if($ukm_id): ?>        <?php if(isset($_GET['success'])): ?>
            <div class="alert <?php echo $_GET['success'] == 1 ? 'alert-success' : 'alert-success'; ?>">
                <?php 
                switch($_GET['success']) {
                    case 1:
                        echo 'Transaksi berhasil ditambahkan';
                        break;
                    case 2:
                        echo 'Transaksi berhasil dihapus';
                        break;
                    case 3:
                        echo 'Transaksi berhasil diupdate';
                        break;
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="ukm-selector">
            <form method="GET" action="">
                <label for="ukm">Pilih UKM:</label>
                <select name="ukm_id" id="ukm" onchange="this.form.submit()">
                    <?php foreach($ukms as $ukm): ?>
                        <option value="<?php echo $ukm['id']; ?>" <?php echo ($ukm['id'] == $ukm_id) ? 'selected' : ''; ?>>
                            <?php echo $ukm['nama_ukm']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        
        <div class="transaksi-container">            <div class="transaksi-form">
                <h2><?php echo $edit_transaksi ? 'Edit Transaksi' : 'Tambah Transaksi Baru'; ?></h2>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $edit_transaksi ? 'edit' : 'add'; ?>">
                    <input type="hidden" name="ukm_id" value="<?php echo $ukm_id; ?>">
                    <?php if ($edit_transaksi): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_transaksi['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="jenis">Jenis Transaksi:</label>
                        <select name="jenis" id="jenis" required>
                            <option value="">Pilih Jenis</option>
                            <option value="pemasukan" <?php echo ($edit_transaksi && $edit_transaksi['jenis'] == 'pemasukan') ? 'selected' : ''; ?>>Pemasukan</option>
                            <option value="pengeluaran" <?php echo ($edit_transaksi && $edit_transaksi['jenis'] == 'pengeluaran') ? 'selected' : ''; ?>>Pengeluaran</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="kategori">Kategori:</label>
                        <input type="text" name="kategori" id="kategori" required placeholder="Contoh: Iuran, Sponsorship, Konsumsi, dll" value="<?php echo $edit_transaksi ? htmlspecialchars($edit_transaksi['kategori']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="jumlah">Jumlah (Rp):</label>
                        <input type="number" name="jumlah" id="jumlah" required min="0" value="<?php echo $edit_transaksi ? $edit_transaksi['jumlah'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="tanggal">Tanggal:</label>
                        <input type="date" name="tanggal" id="tanggal" required value="<?php echo $edit_transaksi ? $edit_transaksi['tanggal'] : date('Y-m-d'); ?>">
                    </div>
                      <div class="form-group">
                        <label for="keterangan">Keterangan:</label>
                        <textarea name="keterangan" id="keterangan" rows="3"><?php echo $edit_transaksi ? htmlspecialchars($edit_transaksi['keterangan']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Bukti Transaksi (Opsional):</label>
                        <div class="image-input-container">
                            <div class="image-input-tabs">
                                <button type="button" class="tab-btn active" onclick="showImageTab('upload')">Upload File</button>
                                <button type="button" class="tab-btn" onclick="showImageTab('url')">URL Gambar</button>
                            </div>
                            
                            <div id="upload-tab" class="image-input-tab active">
                                <input type="file" name="image_upload" id="image_upload" accept="image/*" onchange="previewImage(this)">
                                <small class="help-text">Upload gambar bukti transaksi (JPEG, PNG, GIF - Max 5MB)</small>
                            </div>
                            
                            <div id="url-tab" class="image-input-tab">
                                <input type="url" name="image_url" id="image_url" placeholder="https://example.com/image.jpg" 
                                       value="<?php echo ($edit_transaksi && filter_var($edit_transaksi['image'], FILTER_VALIDATE_URL)) ? htmlspecialchars($edit_transaksi['image']) : ''; ?>"
                                       onchange="previewImageUrl(this)">
                                <small class="help-text">Masukkan URL gambar dari internet</small>
                            </div>
                            
                            <div id="image-preview" class="image-preview">
                                <?php if ($edit_transaksi && !empty($edit_transaksi['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($edit_transaksi['image']); ?>" alt="Current transaction image" />
                                    <button type="button" onclick="clearImagePreview()" class="clear-preview">×</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><?php echo $edit_transaksi ? 'Update Transaksi' : 'Simpan Transaksi'; ?></button>
                        <?php if ($edit_transaksi): ?>
                            <a href="transaksi.php?ukm_id=<?php echo $ukm_id; ?>" class="btn btn-secondary">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="transaksi-list">
                <h2>Daftar Transaksi</h2>
                
                  <table id="transaksiTable">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>Jenis</th>
                            <th>Jumlah</th>
                            <th>Keterangan</th>
                            <th>Bukti</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($transaksi) > 0): ?>
                            <?php foreach($transaksi as $t): ?>                                <tr class="<?php echo $t['jenis']; ?>">
                                    <td><?php echo date('d/m/Y', strtotime($t['tanggal'])); ?></td>
                                    <td><?php echo $t['kategori']; ?></td>
                                    <td><?php echo ucfirst($t['jenis']); ?></td>
                                    <td>Rp <?php echo number_format($t['jumlah'], 0, ',', '.'); ?></td>
                                    <td><?php echo $t['keterangan']; ?></td>
                                    <td>
                                        <?php if (!empty($t['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($t['image']); ?>" 
                                                 alt="Transaction image" 
                                                 class="transaction-image" 
                                                 onclick="showImageModal('<?php echo htmlspecialchars($t['image']); ?>')" />
                                        <?php else: ?>
                                            <span style="color: #999; font-style: italic;">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="transaksi.php?ukm_id=<?php echo $ukm_id; ?>&edit_id=<?php echo $t['id']; ?>" class="btn-edit">Edit</a>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                            <input type="hidden" name="ukm_id" value="<?php echo $ukm_id; ?>">
                                            <button type="submit" class="btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data">Belum ada transaksi</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>            </div>
        </div>
        
        <!-- Image Modal -->
        <div id="imageModal" class="image-modal">
            <span class="close" onclick="closeImageModal()">&times;</span>
            <img id="modalImage" alt="Transaction image" />
        </div>
        
        <script>
            $(document).ready(function() {
                // Search and filter transactions
                function filterTable() {
                    const searchValue = $('#searchTransaksi').val().toLowerCase();
                    const jenisValue = $('#filterJenis').val().toLowerCase();
                    
                    $('#transaksiTable tbody tr').each(function() {
                        const row = $(this);
                        
                        // Skip the "no data" row
                        if (row.hasClass('no-data') || row.hasClass('no-data-filtered')) {
                            return;
                        }

                        const rowText = row.text().toLowerCase();
                        
                        // Get the text content of the 'Jenis' column (assuming it's the 3rd column, index 2)
                        const rowJenisText = row.find('td').eq(2).text().toLowerCase().trim();
                        
                        const matchesSearch = searchValue === '' || rowText.includes(searchValue);
                        // Check if the rowJenisText contains the selected filter value (or if filter is empty)
                        const matchesJenis = jenisValue === '' || rowJenisText.includes(jenisValue);
                        
                        row.toggle(matchesSearch && matchesJenis);
                    });
                    
                    // Check if any rows are visible
                    const visibleRows = $('#transaksiTable tbody tr:visible').not('.no-data, .no-data-filtered').length;
                    
                    // Remove existing "no data" message
                    $('#transaksiTable tbody tr.no-data-filtered').remove();
                    
                    // Add "no data" message if no rows are visible
                    if (visibleRows === 0) {
                        $('#transaksiTable tbody').append(
                            '<tr class="no-data-filtered"><td colspan="6" class="no-data">Tidak ada transaksi yang sesuai dengan filter</td></tr>'
                        );
                    }
                }
                
                // Add event listeners
                $('#searchTransaksi').on('keyup', filterTable);
                $('#filterJenis').on('change', filterTable);
                
                // Initial filter
                filterTable();
            });
        </script>
    <?php else: ?>
        <div class="no-ukm">
            <p>Tidak ada UKM yang tersedia. Silakan hubungi administrator.</p>
        </div>
    <?php endif; ?>
        </section>
    </div>
</div>

<script>
// Image input functionality
function showImageTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.image-input-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
    
    // Clear the other input
    if (tabName === 'upload') {
        document.getElementById('image_url').value = '';
    } else {
        document.getElementById('image_upload').value = '';
    }
    
    clearImagePreview();
}

function previewImage(input) {
    const preview = document.getElementById('image-preview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Image preview" />
                <button type="button" onclick="clearImagePreview()" class="clear-preview">×</button>
            `;
        }
        
        reader.readAsDataURL(input.files[0]);
        
        // Clear URL input
        document.getElementById('image_url').value = '';
    }
}

function previewImageUrl(input) {
    const preview = document.getElementById('image-preview');
    const url = input.value.trim();
    
    if (url && isValidImageUrl(url)) {
        preview.innerHTML = `
            <img src="${url}" alt="Image preview" onerror="this.parentElement.innerHTML='<p>Error loading image</p>'" />
            <button type="button" onclick="clearImagePreview()" class="clear-preview">×</button>
        `;
        
        // Clear file input
        document.getElementById('image_upload').value = '';
    } else if (url) {
        preview.innerHTML = '<p style="color: red;">Invalid image URL</p>';
    } else {
        clearImagePreview();
    }
}

function clearImagePreview() {
    document.getElementById('image-preview').innerHTML = '';
}

function isValidImageUrl(url) {
    try {
        const urlObj = new URL(url);
        const extension = urlObj.pathname.split('.').pop().toLowerCase();
        return ['jpg', 'jpeg', 'png', 'gif'].includes(extension);
    } catch {
        return false;
    }
}

// Modal functionality for viewing transaction images
function showImageModal(src) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    modal.classList.add('show');
    modalImg.src = src;
}

function closeImageModal() {
    document.getElementById('imageModal').classList.remove('show');
}

// Close modal when clicking outside the image
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeImageModal();
            }
        });
    }
});
</script>