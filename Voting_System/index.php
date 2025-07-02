<?php
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="container-fluid hero position-relative" style="background: rgb(255,255,255);
  background: linear-gradient(73deg, rgba(255,255,255,1) 8%, rgba(28, 29, 60, 0.207));">
  <div class="overlay"></div>

    <div class="container px-4 py-5">
        <div class="row flex-lg-row-reverse align-items-center justify-content-center g-2 py-5">
            <div id="carouselExampleIndicators" class="carousel slide col-10 col-sm-8 col-lg-6" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                </div>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="Assets/images/z@u.jpg" class="d-block mx-lg-auto img-fluid" style="scale: 70%; border-radius: 2rem 0 2rem;" alt="Zetech Campus" loading="lazy">
                    </div>
                    <div class="carousel-item">
                        <img src="Assets/images/@zu.jpg" class="d-block mx-lg-auto img-fluid" style="scale: 70%; border-radius: 2rem 0 2rem;" alt="Student Life" loading="lazy">
                    </div>
                    <div class="carousel-item">
                        <img src="Assets/images/zu.jpeg" class="d-block mx-lg-auto img-fluid" style="scale: 70%; border-radius: 2rem 0 2rem;" alt="Campus Activities" loading="lazy">
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
            <div class="col-lg-6">
                <h1 class="display-5 fw-bold text-body-emphasis lh-2 mb-3">Zetech University Online Voting System</h1>
                <p class="lead">Welcome to Zetech University's secure online voting platform. Cast your vote easily and securely for student leadership positions and other important university decisions.</p>
                <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a type="button" class="btn text-white btn-lg px-4 me-md-2" style="background-color: #1C1D3C;" href="./dashboard/voter/vote.php">Cast Your Vote</a>
                        <a type="button" class="btn btn-outline-secondary btn-lg px-4" href="results.php">View Results</a>
                    <?php else: ?>
                        <a type="button" class="btn text-white btn-lg px-4 me-md-2" style="background-color: #1C1D3C;" href="auth/index.php">Login to Vote</a>
                        <a type="button" class="btn btn-outline-secondary btn-lg px-4" href="auth/signup.php">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- add image overlay to hero section between back and the content -->
<style>
    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background:url(Assets/Img/Mission/baptism.jpg);
        background-position: center;
        background-size: cover;
        background-blend-mode: overlay;
        opacity: 0.5;
        z-index: -1;
    }
</style>

<?php
include 'includes/footer.php';
?>