<?php
class BoardModel {
    private $board = [
        ['name' => 'Start', 'type' => 'start', 'color' => 'white'],
        ['name' => 'Jakarta', 'price' => 400, 'type' => 'property', 'color' => 'blue'],
        ['name' => 'Dana Umum', 'type' => 'community_chest', 'color' => 'white'],
        ['name' => 'Surabaya', 'price' => 350, 'type' => 'property', 'color' => 'blue'],
        ['name' => 'Pajak', 'price' => 200, 'type' => 'tax', 'color' => 'white'],
        ['name' => 'Stasiun Gambir', 'price' => 200, 'type' => 'station', 'color' => 'white'],
        ['name' => 'Bandung', 'price' => 300, 'type' => 'property', 'color' => 'green'],
        ['name' => 'Kesempatan', 'type' => 'chance', 'color' => 'white'],
        ['name' => 'Yogyakarta', 'price' => 280, 'type' => 'property', 'color' => 'green'],
        ['name' => 'Semarang', 'price' => 260, 'type' => 'property', 'color' => 'green'],
        ['name' => 'Penjara', 'type' => 'jail', 'color' => 'white'],
        ['name' => 'Medan', 'price' => 240, 'type' => 'property', 'color' => 'red'],
        ['name' => 'PLN', 'price' => 150, 'type' => 'utility', 'color' => 'white'],
        ['name' => 'Palembang', 'price' => 220, 'type' => 'property', 'color' => 'red'],
        ['name' => 'Padang', 'price' => 200, 'type' => 'property', 'color' => 'red'],
        ['name' => 'Stasiun Pasar Turi', 'price' => 200, 'type' => 'station', 'color' => 'white'],
        ['name' => 'Makassar', 'price' => 180, 'type' => 'property', 'color' => 'yellow'],
        ['name' => 'Dana Umum', 'type' => 'community_chest', 'color' => 'white'],
        ['name' => 'Manado', 'price' => 160, 'type' => 'property', 'color' => 'yellow'],
        ['name' => 'Kendari', 'price' => 140, 'type' => 'property', 'color' => 'yellow'],
        ['name' => 'Parkir Bebas', 'type' => 'free_parking', 'color' => 'white'],
        ['name' => 'Denpasar', 'price' => 120, 'type' => 'property', 'color' => 'orange'],
        ['name' => 'Kesempatan', 'type' => 'chance', 'color' => 'white'],
        ['name' => 'Mataram', 'price' => 100, 'type' => 'property', 'color' => 'orange'],
        ['name' => 'Kupang', 'price' => 100, 'type' => 'property', 'color' => 'orange'],
        ['name' => 'Stasiun Tugu', 'price' => 200, 'type' => 'station', 'color' => 'white'],
        ['name' => 'Banjarmasin', 'price' => 80, 'type' => 'property', 'color' => 'pink'],
        ['name' => 'Balikpapan', 'price' => 60, 'type' => 'property', 'color' => 'pink'],
        ['name' => 'PDAM', 'price' => 150, 'type' => 'utility', 'color' => 'white'],
        ['name' => 'Pontianak', 'price' => 60, 'type' => 'property', 'color' => 'pink'],
        ['name' => 'Masuk Penjara', 'type' => 'go_to_jail', 'color' => 'white'],
        ['name' => 'Ambon', 'price' => 50, 'type' => 'property', 'color' => 'brown'],
        ['name' => 'Ternate', 'price' => 50, 'type' => 'property', 'color' => 'brown'],
        ['name' => 'Dana Umum', 'type' => 'community_chest', 'color' => 'white'],
        ['name' => 'Jayapura', 'price' => 40, 'type' => 'property', 'color' => 'brown'],
        ['name' => 'Stasiun Bandung', 'price' => 200, 'type' => 'station', 'color' => 'white'],
        ['name' => 'Kesempatan', 'type' => 'chance', 'color' => 'white'],
        ['name' => 'Sorong', 'price' => 40, 'type' => 'property', 'color' => 'cyan'],
        ['name' => 'Pajak Mewah', 'price' => 100, 'type' => 'tax', 'color' => 'white'],
        ['name' => 'Manokwari', 'price' => 40, 'type' => 'property', 'color' => 'cyan']
    ];

    public function getBoard() {
        return $this->board;
    }
}
