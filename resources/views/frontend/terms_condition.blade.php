@extends('frontend.layouts.app')

@section('content')
<style>
/* ==========================
   Policy Page Styles
========================== */
.policy-section {
    background-color: #f9fafb;
    color: #111213 !important; /* fallback for text */
    padding: 60px 0;
}

.policy-section h1 {
    font-size: 32px;
    font-weight: 700;
    color: #111213;
    text-align: center;
    margin-bottom: 40px;
}

.policy-content {
    background: #ffffff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    line-height: 1.8;
    font-size: 16px;
    color: #111213 !important; /* force text color */
}

/* Headings inside policy content */
.policy-content h1,
.policy-content h2,
.policy-content h3,
.policy-content h4,
.policy-content h5,
.policy-content h6 {
    margin-top: 25px;
    margin-bottom: 12px;
    font-weight: 600;
    color: #111213 !important;
}

/* Paragraphs */
.policy-content p,
.policy-content span,
.policy-content li {
    margin-bottom: 16px;
    color: #111213 !important;
}

/* Lists */
.policy-content ul,
.policy-content ol {
    padding-left: 20px;
    margin-bottom: 16px;
}

/* Links */
.policy-content a {
    color: #2563eb;
    text-decoration: underline;
}

.policy-content a:hover {
    color: #1d4ed8;
}

/* Override inline white color saved from editor */
.policy-content [style*="color"] {
    color: #111213 !important;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .policy-section h1 {
        font-size: 24px;
    }

    .policy-content {
        padding: 20px;
        font-size: 15px;
    }
}
</style>

<section class="policy-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <h1>{{ $pageTitle ?? 'Terms & Conditions' }}</h1>

                <div class="policy-content">
                    {!! $termsCondition->value ?? '<p>Content not available.</p>' !!}
                </div>

            </div>
        </div>
    </div>
</section>
@endsection
