<?php
$adminCtrl = file_get_contents('app/controllers/Admin.php');

// We need to add the new methods to Admin.php:
// sessions(), database(), password()
// And update dashboard() and settings().

$adminCtrl = str_replace(
    "    public function settings() {", 
    "    public function sessions() {
        \$this->requireLogin();
        \$db = new Database;
        \$db->query(\"SELECT s.*, COUNT(p.id) as player_count FROM sessions s LEFT JOIN players p ON p.session_id = s.id GROUP BY s.id ORDER BY s.created_at DESC\");
        \$data['sessions'] = \$db->resultSet();
        \$data['judul'] = 'Sesi Aktif';
        \$data['admin'] = \$_SESSION['admin_username'];
        \$this->view('admin/sessions', \$data);
    }

    public function database() {
        \$this->requireLogin();
        \$data['judul'] = 'Database';
        \$data['admin'] = \$_SESSION['admin_username'];
        \$this->view('admin/database', \$data);
    }

    public function password() {
        \$this->requireLogin();
        \$data['judul'] = 'Ganti Password';
        \$data['admin'] = \$_SESSION['admin_username'];
        \$this->view('admin/password', \$data);
    }

    public function settings() {",
    $adminCtrl
);

$adminCtrl = str_replace(
    "        header('Location: ' . BASEURL . '/admin/dashboard'); exit;",
    "        \$data['settings'] = \$this->model('SettingsModel')->getAll();
        \$data['judul'] = 'Pengaturan Game';
        \$data['admin'] = \$_SESSION['admin_username'];
        \$this->view('admin/settings', \$data);",
    $adminCtrl
);

// Update saveCard to support image upload
$saveCardTarget = "            if (\$id > 0) {
                \$db->query(\"UPDATE cards SET text=:text, type=:type, effect_type=:et, effect_value=:ev, is_active=:ia WHERE id=:id\");";
$saveCardReplacement = "            \$image_url = '';
            if (isset(\$_FILES['image']) && \$_FILES['image']['error'] == 0) {
                \$ext = pathinfo(\$_FILES['image']['name'], PATHINFO_EXTENSION);
                \$filename = time() . '_' . rand(1000,9999) . '.' . \$ext;
                move_uploaded_file(\$_FILES['image']['tmp_name'], 'public/img/cards/' . \$filename);
                \$image_url = \$filename;
            }

            if (\$id > 0) {
                if (\$image_url) {
                    \$db->query(\"UPDATE cards SET text=:text, type=:type, effect_type=:et, effect_value=:ev, is_active=:ia, image_url=:img WHERE id=:id\");
                    \$db->bind('img', \$image_url);
                } else {
                    \$db->query(\"UPDATE cards SET text=:text, type=:type, effect_type=:et, effect_value=:ev, is_active=:ia WHERE id=:id\");
                }
";
$adminCtrl = str_replace($saveCardTarget, $saveCardReplacement, $adminCtrl);

$insertTarget = "                \$db->query(\"INSERT INTO cards (text, type, effect_type, effect_value, is_active) VALUES (:text,:type,:et,:ev,:ia)\");";
$insertReplacement = "                \$db->query(\"INSERT INTO cards (text, type, effect_type, effect_value, is_active, image_url) VALUES (:text,:type,:et,:ev,:ia,:img)\");
                \$db->bind('img', \$image_url);";
$adminCtrl = str_replace($insertTarget, $insertReplacement, $adminCtrl);

file_put_contents('app/controllers/Admin.php', $adminCtrl);

// Create public/img/cards dir
@mkdir('public/img/cards', 0777, true);
echo "Admin controller updated\n";
