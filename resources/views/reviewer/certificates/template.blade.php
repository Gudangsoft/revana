<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviewer Certificate</title>
    <style>
        @page {
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Georgia', serif;
            background: linear-gradient(135deg, #C9A961 0%, #E6D39E 100%);
            width: 297mm;
            height: 210mm;
            position: relative;
        }
        
        .certificate-container {
            width: 100%;
            height: 100%;
            padding: 40px 60px;
            box-sizing: border-box;
            position: relative;
        }
        
        .certificate-border {
            border: 15px solid #8B6914;
            border-radius: 30px;
            padding: 50px;
            background: white;
            height: 100%;
            position: relative;
            box-shadow: inset 0 0 50px rgba(201, 169, 97, 0.3);
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 200px;
            color: rgba(201, 169, 97, 0.08);
            font-weight: bold;
            z-index: 0;
            letter-spacing: 20px;
        }
        
        .content {
            position: relative;
            z-index: 1;
            text-align: center;
        }
        
        .header {
            margin-bottom: 20px;
        }
        
        .title {
            font-size: 48px;
            font-weight: bold;
            color: #C9A961;
            letter-spacing: 8px;
            margin: 0;
            text-transform: uppercase;
        }
        
        .subtitle {
            font-size: 36px;
            font-weight: bold;
            color: #8B6914;
            letter-spacing: 6px;
            margin: 5px 0 0 0;
            text-transform: uppercase;
        }
        
        .date-section {
            position: absolute;
            top: 50px;
            right: 80px;
            text-align: right;
            font-size: 24px;
            color: #8B6914;
            font-weight: bold;
        }
        
        .date-section .year {
            font-size: 48px;
            display: block;
            margin-bottom: -10px;
        }
        
        .date-section .date-detail {
            font-size: 20px;
            display: block;
        }
        
        .award-text {
            font-size: 20px;
            color: #333;
            margin: 30px 0 20px 0;
        }
        
        .reviewer-name {
            font-size: 42px;
            font-weight: bold;
            color: #C9A961;
            margin: 20px 0;
            padding: 15px;
            letter-spacing: 3px;
            text-transform: uppercase;
        }
        
        .recognition-text {
            font-size: 18px;
            color: #333;
            margin: 20px 0;
            font-style: italic;
        }
        
        .article-title {
            font-size: 24px;
            color: #C9A961;
            margin: 25px auto;
            max-width: 80%;
            line-height: 1.5;
            font-weight: 600;
        }
        
        .appreciation-text {
            font-size: 16px;
            color: #555;
            margin: 25px auto;
            max-width: 85%;
            line-height: 1.8;
            text-align: center;
        }
        
        .footer {
            position: absolute;
            bottom: 50px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 80px;
        }
        
        .logo {
            width: 120px;
            height: auto;
        }
        
        .logo-text {
            font-size: 28px;
            font-weight: bold;
            color: #C9A961;
            letter-spacing: 4px;
        }
        
        .divider {
            width: 200px;
            height: 3px;
            background: linear-gradient(to right, transparent, #C9A961, transparent);
            margin: 15px auto;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="certificate-border">
            <div class="watermark">APJI</div>
            
            <div class="content">
                <!-- Date Section -->
                <div class="date-section">
                    <span class="year">{{ $year }}</span>
                    <span class="date-detail">{{ $date }} {{ $month }}</span>
                </div>
                
                <!-- Header -->
                <div class="header">
                    <h1 class="title">REVIEWER</h1>
                    <h2 class="subtitle">CERTIFICATE</h2>
                </div>
                
                <div class="divider"></div>
                
                <!-- Award Text -->
                <p class="award-text">This certificate is awarded to</p>
                
                <!-- Reviewer Name -->
                <div class="reviewer-name">{{ $reviewer_name }}</div>
                
                <!-- Recognition Text -->
                <p class="recognition-text">in recognition of their hard work as a peer reviewer for</p>
                
                <!-- Article Title -->
                <div class="article-title">{{ $article_title }}</div>
                
                <div class="divider"></div>
                
                <!-- Appreciation -->
                <p class="appreciation-text">
                    Thank you for your contribution to the journal. The dedication of our reviewers is invaluable in<br>
                    safeguarding the quality and high standard of academic integrity in the research we publish.
                </p>
                
                <!-- Footer -->
                <div class="footer">
                    <div>
                        <div class="logo-text">APJI</div>
                    </div>
                    <div>
                        <div class="logo-text">SIPERA</div>
                        <small style="font-size: 11px; color: #888;">Sistem Insentif dan Penghargaan Reviewer APJI</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
