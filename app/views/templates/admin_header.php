<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['judul'] ?? 'Admin Dashboard' ?> — Monopoly</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #020617; color: white; min-height: 100vh; }
        .active-link { background: rgba(59,130,246,0.2); border-color: rgba(59,130,246,0.5); color: #60a5fa; }
    </style>
</head>
<body>
<?php if (isset($_GET['saved'])): ?>
<script>document.addEventListener('DOMContentLoaded', () => Swal.fire({title:'Tersimpan!',icon:'success',background:'#0f172a',color:'#fff',timer:1500,showConfirmButton:false}));</script>
<?php endif; ?>
<?php if (isset($_GET['repaired'])): ?>
<script>document.addEventListener('DOMContentLoaded', () => Swal.fire({title:'Database Diperbaiki!',text:'Selesai.',icon:'success',background:'#0f172a',color:'#fff'}));</script>
<?php endif; ?>
<div class="flex min-h-screen">
