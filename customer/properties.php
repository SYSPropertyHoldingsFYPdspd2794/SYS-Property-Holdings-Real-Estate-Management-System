<?php
/**
 * PROJECT: SYS Property Holdings
 * MODULE: Customer - Property Catalog (US13, US19)
 * DESCRIPTION: Fixed Image Loading using mapping between DB types and folder names.
 */

// 1. INITIALIZATION: Header provides $conn and $root_prefix
include_once '../includes/header.php';
include_once '../includes/header.php';
/** @var string $root_prefix */
/** @var mysqli $conn */
require_once '../includes/auth_check.php';

// 2. SECURITY: Load team auth logic
require_once '../includes/auth_check.php';
protect_customer_page('CUSTOMER', $conn);
?>

<div class="container my-5">
    <div class="row mb-5">
        <div class="col-md-12 text-center">
            <h2 class="fw-bold display-5 text-dark">Properties Catalog</h2>
            <p class="lead text-secondary">Discover exclusive listings in our O2O management system.</p>
            <hr class="w-25 mx-auto bg-primary" style="height: 3px; border-radius: 5px; opacity: 1;">
        </div>
    </div>

    <div class="row">
        <?php
        /**
         * FETCH DATA FROM DATABASE
         */
        $sql = "SELECT * FROM properties WHERE status = 'ACTIVE' OR status = 'AVAILABLE' ORDER BY property_id DESC";
        $result = $conn->query($sql);

        if (!$result) {
            echo '<div class="col-12 alert alert-danger">SQL Error: ' . htmlspecialchars($conn->error) . '</div>';
        } elseif ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                
                // --- IMAGE MAPPING LOGIC ---
                // Get data from DB
                $dbType = strtolower(trim($row['property_type']));
                $stateName = ucfirst(strtolower(trim($row['state']))); // e.g., "Johor"
                
                $folder = "";
                $filePrefix = "";

                /**
                 * Mapping Logic based on your provided screenshots:
                 * DB: Commercial -> Folder: 3. COMMERCIAL, File: Commercial
                 * DB: Standard -> Folder: 4. TERRACE, File: Terrace
                 * DB: Affordable -> Folder: 1. APARTMENT, File: Apartment
                 * DB: Bungalow -> Folder: 2. BUNGALOW, File: Bungalow
                 */
                switch($dbType) {
                    case 'commercial':
                        $folder = "3. COMMERCIAL/";
                        $filePrefix = "Commercial";
                        break;
                    case 'standard':
                        $folder = "4. TERRACE/";
                        $filePrefix = "Terrace";
                        break;
                    case 'affordable':
                        $folder = "1. APARTMENT/";
                        $filePrefix = "Apartment";
                        break;
                    case 'bungalow':
                        $folder = "2. BUNGALOW/";
                        $filePrefix = "Bungalow";
                        break;
                    case 'apartment':
                        $folder = "1. APARTMENT/";
                        $filePrefix = "Apartment";
                        break;
                    case 'terrace':
                        $folder = "4. TERRACE/";
                        $filePrefix = "Terrace";
                        break;
                    default:
                        $folder = ""; 
                        $filePrefix = ucfirst($dbType);
                }

                // Directory path
                $catalogDir = $root_prefix . "C:\xampp\htdocs\SYS_Property\SYS-Property-Holdings-Real-Estate-Management-System\SYS Property Catalog";
                $finalImg = $catalogDir . "placeholder.jpg"; // Default fallback

                // Scan for the file with different extensions
                $extensions = ['jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG'];
                $fileName = $filePrefix . " - " . $stateName; // e.g., "Apartment - Johor"

                foreach ($extensions as $ext) {
                    $testPath = $catalogDir . $folder . $fileName . "." . $ext;
                    // Check if file exists on the server
                    if (file_exists($testPath)) {
                        $finalImg = $testPath;
                        break;
                    }
                }

                // Data Formatting
                $formattedPrice = number_format($row['price'], 2);
                $availableUnits = isset($row['available_units']) ? $row['available_units'] : 0;
                ?>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                        
                        <div class="position-relative bg-light" style="height: 250px;">
                            <img src="<?php echo htmlspecialchars($finalImg); ?>" 
                                 class="w-100 h-100" 
                                 alt="Property" 
                                 style="object-fit: cover; object-position: center;"
                                 onerror="this.src='<?php echo $catalogDir; ?>placeholder.jpg';">
                            
                            <span class="badge bg-primary position-absolute top-0 end-0 m-3 shadow">
                                <?php echo htmlspecialchars($row['property_type']); ?>
                            </span>
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <h5 class="card-title fw-bold text-dark mb-1 text-truncate">
                                <?php echo htmlspecialchars($row['project_name']); ?>
                            </h5>
                            <p class="text-muted small mb-4">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i> <?php echo htmlspecialchars($stateName); ?>
                            </p>
                            
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block fw-bold" style="font-size: 0.7rem;">PRICE FROM</small>
                                    <h4 class="text-success fw-bold mb-0">RM <?php echo $formattedPrice; ?></h4>
                                </div>
                                <a href="property_detail.php?id=<?php echo $row['property_id']; ?>" 
                                   class="btn btn-dark rounded-pill px-4 shadow-sm">
                                   Details
                                </a>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-0 py-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-door-open me-1"></i> Units Available: 
                                <strong class="text-dark"><?php echo $availableUnits; ?></strong>
                            </small>
                        </div>
                    </div>
                </div>

                <?php
            }
        } else {
            echo '<div class="col-12 text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <p class="lead text-muted">No properties available.</p>
                  </div>';
        }
        ?>
    </div>
</div>

<?php include_once $root_prefix . 'includes/footer.php'; ?>