<?php
// --- ১. ডাটাবেস কানেকশন ---
$db_host = 'localhost';     // সাধারণত localhost
$db_user = 'root';          // আপনার ডাটাবেস ইউজারনেম
$db_pass = '';              // আপনার ডাটাবেস পাসওয়ার্ড
$db_name = 'blog'; // আপনার ডাটাবেসের নাম

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4'); // বাংলা সাপোর্টের জন্য

// --- ২. প্যাজিনেশন ভেরিয়েবল ---
$posts_per_page = 5; // আপনার রিকোয়ারমেন্ট: প্রতি পৃষ্ঠায় ৫টি পোস্ট

// বর্তমান পেজ নম্বর (URL থেকে ?page=X নিবে, না থাকলে ১ ধরবে)
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

// SQL LIMIT এর জন্য OFFSET গণনা
$offset = ($current_page - 1) * $posts_per_page;

// --- ৩. মোট পোস্ট সংখ্যা গণনা (প্যাজিনেশন লিঙ্ক দেখানোর জন্য) ---
$total_posts_query = mysqli_query($conn, "SELECT COUNT(id) AS total FROM posts");
$total_posts_row = mysqli_fetch_assoc($total_posts_query);
$total_posts = $total_posts_row['total'];

$total_pages = ceil($total_posts / $posts_per_page);

// --- ৪. বর্তমান পৃষ্ঠার জন্য পোস্টগুলো আনা ---
// ORDER BY id DESC দিলে নতুন পোস্ট আগে দেখাবে
$sql = "SELECT * FROM posts ORDER BY post_date DESC LIMIT $posts_per_page OFFSET $offset";
$result = mysqli_query($conn, $sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog | Md. Zahidur Rahman</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* == আপনার বিদ্যমান সমস্ত স্টাইল অপরিবর্তিত == */
        body {
            padding-top: 70px;
            color: #212121  !important;
        }
        .main-content { margin-top: 2rem; }
        .sidebar-sticky {
            position: sticky;
            top: 90px;
            height: calc(100vh - 90px);
            overflow-y: auto;
        }
        .profile-pic { max-width: 250px; border: 1px solid #ddd; }
        .contact-info { font-size: 0.95rem; }
        .contact-info li { display: flex; align-items: center; margin-bottom: 0.5rem; }
        .contact-info i { width: 20px; margin-right: 10px; text-align: center; color: #555; }
        .contact-info a { text-decoration: none; color: #0d6efd; }
        .contact-info a:hover { text-decoration: underline; }
        .content-section { padding-top: 80px; margin-top: -70px; }
        .section-title {
            font-weight: 700;
            border-bottom: 2px solid #eee;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 991.98px) {
            .sidebar-sticky { position: static; height: auto; margin-bottom: 2rem; }
            body { padding-top: 56px; }
            .content-section { padding-top: 70px; margin-top: -56px; }
        }
        
        /* == ব্লগ তালিকার জন্য নতুন স্টাইল == */
        .blog-post h3 a {
            text-decoration: none;
            color: #1c4587; /* আপনার বিদ্যমান লিঙ্ক স্টাইল */
        }
        .blog-post h3 a:hover {
            text-decoration: underline !important;
        }
        .blog-post .read-more-link {
            font-weight: 600;
            text-decoration: none;
        }
         .blog-post .read-more-link:hover {
            text-decoration: underline !important;
         }
    </style>
</head>

<body>

    <nav id="mainNavbar" class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.html">Md. Zahidur Rahman</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">About</a>
                    </li>
                
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="blog.php">Blog</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container main-content">
        <div class="row">
            
            <div class="col-lg-12">
                <section id="blog-main">
                    <h2 class="section-title" style="color: #1c4587; font-family: Lato, Arial, sans-serif; font-weight: 700;">Personal Blog</h2>

                    <?php
                    // --- ৫. ডাইনামিক্যালি পোস্ট দেখানোর লুপ ---
                    if (mysqli_num_rows($result) > 0) {
                        $is_first_post = true; // প্রথম পোস্টের আগে <hr> না দেখানোর জন্য
                        
                        while($row = mysqli_fetch_assoc($result)) {
                            
                            // প্রথম পোস্ট না হলে একটি <hr> দেখাও
                            if (!$is_first_post) {
                                echo '<hr class="my-4">';
                            }
                            $is_first_post = false;

                            // ডাটাবেস থেকে পাওয়া তারিখ ফরম্যাট করা
                            $date = date_create($row['post_date']);
                            $formatted_date = date_format($date, 'F j, Y'); // যেমন: October 29, 2025

                            // ডাটাবেস থেকে পাওয়া তথ্য দিয়ে HTML তৈরি করা
                            echo '<article class="blog-post mb-5">';
                            echo '  <h3 class="h4" style="font-weight: 600;"><a href="' . htmlspecialchars($row['post_link']) . '">' . htmlspecialchars($row['title']) . '</a></h3>';
                            echo '  <p class="text-muted" style="font-size: 0.9rem;">Posted on ' . $formatted_date . '</p>';
                            echo '  <p style="text-align: justify;">' . htmlspecialchars($row['snippet']) . '</p>';
                            echo '  <a href="' . htmlspecialchars($row['post_link']) . '" class="read-more-link">Read More &rarr;</a>';
                            echo '</article>';
                        }
                    } else {
                        // কোনো পোস্ট না পাওয়া গেলে
                        echo '<p>No posts found.</p>';
                    }
                    ?>
                    
                    <hr class="my-4">
                    
                    <nav aria-label="Blog pagination" class="mt-5">
                      <ul class="pagination justify-content-center">
                        
                        <?php if($current_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="blog.php?page=<?php echo $current_page - 1; ?>">Previous</a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php if($i == $current_page) echo 'active'; ?>" <?php if($i == $current_page) echo 'aria-current="page"'; ?>>
                                <a class="page-link" href="blog.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if($current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="blog.php?page=<?php echo $current_page + 1; ?>">Next</a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Next</a>
                            </li>
                        <?php endif; ?>

                      </ul>
                    </nav>

                </section>
            </div>
            
        </div>
    </div>

    <footer class="text-center py-4 mt-5 border-top bg-light">
        <p class="mb-0 text-muted">&copy; 2025 Md. Zahidur Rahman</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// ডাটাবেস কানেকশন বন্ধ করা
mysqli_close($conn);
?>