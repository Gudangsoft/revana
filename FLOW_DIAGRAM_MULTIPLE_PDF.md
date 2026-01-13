# 📊 Flow Diagram: Multiple PDF Upload & Merge

## 🔄 Complete Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    REVIEWER WORKFLOW                             │
└─────────────────────────────────────────────────────────────────┘

    ┌──────────────┐
    │  Task Page   │
    │  (SUBMITTED) │
    └──────┬───────┘
           │
           ▼
    ┌──────────────────────────┐
    │ Click: Upload File       │
    │ Revisi Jurnal            │
    └──────────┬───────────────┘
               │
               ▼
    ┌──────────────────────────────────────┐
    │  Modal Opens                          │
    │  - Select multiple PDF files          │
    │  - Ctrl+Click to select many          │
    │  - Max 10 files                       │
    └──────────┬───────────────────────────┘
               │
               ▼
    ┌──────────────────────────────────────┐
    │  File Preview                         │
    │  ✓ file1.pdf (2.5 MB)                 │
    │  ✓ file2.pdf (3.1 MB)                 │
    │  ✓ file3.pdf (4.2 MB)                 │
    └──────────┬───────────────────────────┘
               │
               ▼
    ┌──────────────────────────────────────┐
    │  Click: Upload & Gabungkan PDF       │
    └──────────┬───────────────────────────┘
               │
               ▼
    ┌──────────────────────────────────────┐
    │  VALIDATION                           │
    │  - Check format (PDF only)            │
    │  - Check size (max 10MB per file)     │
    │  - Check count (max 10 files)         │
    └──────────┬───────────────────────────┘
               │
               ├─── ❌ Invalid ──→ [Show Error Message]
               │
               ▼ ✅ Valid
    ┌──────────────────────────────────────┐
    │  UPLOAD FILES                         │
    │  storage/review-revisions/{id}/       │
    │    - revision_1234_1_file1.pdf        │
    │    - revision_1234_2_file2.pdf        │
    │    - revision_1234_3_file3.pdf        │
    └──────────┬───────────────────────────┘
               │
               ▼
    ┌──────────────────────────────────────┐
    │  MERGE PDFs                           │
    │  PdfMergerService::mergePdfs()        │
    │    - Import all pages                 │
    │    - Preserve orientation             │
    │    - Preserve size                    │
    └──────────┬───────────────────────────┘
               │
               ├─── ❌ Merge Failed ──→ [Rollback Files]
               │
               ▼ ✅ Success
    ┌──────────────────────────────────────┐
    │  SAVE TO DATABASE                     │
    │  revision_files: [file1, file2, file3]│
    │  merged_revision_file: merged.pdf     │
    └──────────┬───────────────────────────┘
               │
               ▼
    ┌──────────────────────────────────────┐
    │  SUCCESS MESSAGE                      │
    │  "3 file PDF berhasil diupload        │
    │   dan digabungkan!"                   │
    └──────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                      ADMIN WORKFLOW                              │
└─────────────────────────────────────────────────────────────────┘

    ┌──────────────────┐
    │  Assignment      │
    │  Detail Page     │
    └──────┬───────────┘
           │
           ▼
    ┌──────────────────────────────────────┐
    │  Scroll to: File Revisi Jurnal       │
    └──────────┬───────────────────────────┘
               │
               ▼
    ┌──────────────────────────────────────┐
    │  See Files:                           │
    │  🎯 [Download PDF Gabungan] ← MAIN   │
    │  📁 Individual files (3 files)        │
    └──────────┬───────────────────────────┘
               │
               ▼
    ┌──────────────────────────────────────┐
    │  Click: Download PDF Gabungan        │
    └──────────┬───────────────────────────┘
               │
               ▼
    ┌──────────────────────────────────────┐
    │  GET MERGED PDF                       │
    │  - All pages from all files           │
    │  - Single PDF download                │
    │  - Ready to review                    │
    └──────────────────────────────────────┘
```

---

## 📦 Data Flow

```
┌─────────────────────────────────────────────────────────────┐
│                     DATA FLOW DIAGRAM                        │
└─────────────────────────────────────────────────────────────┘

   Browser                Controller                Service              Storage              Database
     │                        │                        │                    │                    │
     │  Upload files[]        │                        │                    │                    │
     ├───────────────────────>│                        │                    │                    │
     │                        │                        │                    │                    │
     │                        │  Validate              │                    │                    │
     │                        │  - Format: PDF         │                    │                    │
     │                        │  - Size: <10MB         │                    │                    │
     │                        │  - Count: ≤10          │                    │                    │
     │                        │                        │                    │                    │
     │                        │  Store files           │                    │                    │
     │                        ├────────────────────────┼───────────────────>│                    │
     │                        │                        │     file1.pdf      │                    │
     │                        │                        │     file2.pdf      │                    │
     │                        │                        │     file3.pdf      │                    │
     │                        │                        │                    │                    │
     │                        │  mergePdfs()           │                    │                    │
     │                        ├───────────────────────>│                    │                    │
     │                        │                        │  Read files        │                    │
     │                        │                        ├───────────────────>│                    │
     │                        │                        │<───────────────────┤                    │
     │                        │                        │                    │                    │
     │                        │                        │  Import pages      │                    │
     │                        │                        │  - Page 1-5 (file1)│                    │
     │                        │                        │  - Page 1-8 (file2)│                    │
     │                        │                        │  - Page 1-3 (file3)│                    │
     │                        │                        │                    │                    │
     │                        │                        │  Generate merged   │                    │
     │                        │                        │  PDF (16 pages)    │                    │
     │                        │                        │                    │                    │
     │                        │                        │  Save merged.pdf   │                    │
     │                        │                        ├───────────────────>│                    │
     │                        │<───────────────────────┤                    │                    │
     │                        │  Return: merged path   │                    │                    │
     │                        │                        │                    │                    │
     │                        │  Update database       │                    │                    │
     │                        ├────────────────────────┼────────────────────┼───────────────────>│
     │                        │  revision_files: [...]  │                    │     UPDATE         │
     │                        │  merged_revision_file   │                    │  review_results    │
     │                        │                        │                    │                    │
     │<───────────────────────┤                        │                    │                    │
     │  Success message       │                        │                    │                    │
     │                        │                        │                    │                    │
```

---

## 🏗️ System Architecture

```
┌──────────────────────────────────────────────────────────────────────┐
│                        SYSTEM ARCHITECTURE                            │
└──────────────────────────────────────────────────────────────────────┘

┌─────────────┐         ┌─────────────┐         ┌─────────────┐
│   Browser   │◄───────►│  Laravel    │◄───────►│   MySQL     │
│   (Client)  │  HTTP   │  Backend    │  Query  │  Database   │
└─────────────┘         └──────┬──────┘         └─────────────┘
                               │
                               │
                ┌──────────────┼──────────────┐
                │              │              │
                ▼              ▼              ▼
        ┌──────────────┐  ┌─────────┐  ┌──────────────┐
        │  Controller  │  │ Service │  │   Storage    │
        │              │  │         │  │              │
        │ - Validate   │  │  FPDI   │  │ - Individual │
        │ - Store      │  │  FPDF   │  │   PDFs       │
        │ - Merge      │  │         │  │ - Merged PDF │
        └──────────────┘  └─────────┘  └──────────────┘
```

---

## 🔐 Security Flow

```
┌──────────────────────────────────────────────────────────────┐
│                     SECURITY CHECKS                           │
└──────────────────────────────────────────────────────────────┘

    Upload Request
         │
         ▼
    ┌────────────────────┐
    │ 1. Authentication  │  ← User logged in?
    └────────┬───────────┘
             │ ✅
             ▼
    ┌────────────────────┐
    │ 2. Authorization   │  ← Is assigned reviewer?
    └────────┬───────────┘
             │ ✅
             ▼
    ┌────────────────────┐
    │ 3. CSRF Token      │  ← Valid token?
    └────────┬───────────┘
             │ ✅
             ▼
    ┌────────────────────┐
    │ 4. File Type       │  ← PDF only?
    └────────┬───────────┘
             │ ✅
             ▼
    ┌────────────────────┐
    │ 5. File Size       │  ← <10MB per file?
    └────────┬───────────┘
             │ ✅
             ▼
    ┌────────────────────┐
    │ 6. File Count      │  ← ≤10 files?
    └────────┬───────────┘
             │ ✅
             ▼
    ┌────────────────────┐
    │ 7. PDF Validation  │  ← Valid PDF format?
    └────────┬───────────┘
             │ ✅
             ▼
    ┌────────────────────┐
    │ 8. Storage Path    │  ← Safe path?
    └────────┬───────────┘
             │ ✅
             ▼
        [PROCEED]
```

---

## 📁 File Structure

```
┌──────────────────────────────────────────────────────────────┐
│                    FILE ORGANIZATION                          │
└──────────────────────────────────────────────────────────────┘

storage/app/public/review-revisions/
│
├── 15/  ← Assignment ID
│   ├── revision_1705140000_1_introduction.pdf     (2.5 MB)
│   ├── revision_1705140000_2_methodology.pdf      (3.1 MB)
│   ├── revision_1705140000_3_results.pdf          (4.2 MB)
│   ├── revision_1705140000_4_discussion.pdf       (2.8 MB)
│   ├── revision_1705140000_5_conclusion.pdf       (1.5 MB)
│   └── merged_revision_15_42_1705140000.pdf       (14.1 MB) ← MAIN
│
├── 16/  ← Another Assignment
│   └── merged_revision_16_43_1705141000.pdf
│
└── 17/
    └── ...

Database: review_results
┌────┬─────────────┬──────────────────┬──────────────────────┐
│ id │ assignment  │ revision_files   │ merged_revision_file │
├────┼─────────────┼──────────────────┼──────────────────────┤
│ 42 │ 15          │ [file1, file2,   │ merged.pdf           │
│    │             │  file3, ...]     │                      │
└────┴─────────────┴──────────────────┴──────────────────────┘
```

---

## ⚡ Performance Timeline

```
┌──────────────────────────────────────────────────────────────┐
│                   OPERATION TIMELINE                          │
└──────────────────────────────────────────────────────────────┘

Time    Operation                        Status
─────   ──────────────────────────────   ──────────────────
0s      User clicks upload               ▓░░░░░░░░░░░░░░░
1s      Files selected, preview shown    ▓▓░░░░░░░░░░░░░░
2s      User clicks "Upload & Gabungkan" ▓▓▓░░░░░░░░░░░░░
3s      Validation                       ▓▓▓▓░░░░░░░░░░░░
4s      Upload file 1                    ▓▓▓▓▓░░░░░░░░░░░
5s      Upload file 2                    ▓▓▓▓▓▓░░░░░░░░░░
6s      Upload file 3                    ▓▓▓▓▓▓▓░░░░░░░░░
7s      Start merge                      ▓▓▓▓▓▓▓▓░░░░░░░░
8s      Merge in progress                ▓▓▓▓▓▓▓▓▓░░░░░░░
9s      Save merged PDF                  ▓▓▓▓▓▓▓▓▓▓░░░░░░
10s     Update database                  ▓▓▓▓▓▓▓▓▓▓▓░░░░░
11s     Redirect with success            ▓▓▓▓▓▓▓▓▓▓▓▓░░░░
12s     Page reload, show files          ▓▓▓▓▓▓▓▓▓▓▓▓▓░░░
13s     Complete                         ▓▓▓▓▓▓▓▓▓▓▓▓▓▓░░
                                         ▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓░
                                         ▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓

Total: ~10-15 seconds for 5 files (~15MB)
```

---

✅ **All diagrams complete!**

📅 **Date:** January 13, 2026
