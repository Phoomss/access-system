<?php
session_start();
require_once '../../server/user.php';

$user = new User();
$response = $user->getAllUser();

if ($response['success']) {
    $users = $response['data'];
} else {
    $users = [];
    error_log($response['message']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบเข้าออกงาน</title>
    <?php include_once '../layouts/config/libary.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-KyZXEAg3QhqLMpG8r+Knujsl5+5hb7Q5aaC1w8+YdJk=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once '../layouts/sidenav.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include_once '../layouts/navbar.php'; ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">ข้อมูลพนักงาน</h1>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>ชื่อ-นามสกุล</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($users)) : ?>
                                            <?php $count = 1; ?>
                                            <?php foreach ($users as $user) : ?>
                                                <tr>
                                                    <td><?= $count++; ?></td>
                                                    <td><?= htmlspecialchars($user['title']) . htmlspecialchars($user['firstname']) . ' ' . htmlspecialchars($user['surname']); ?></td>
                                                    <td><?= htmlspecialchars($user['phone']); ?></td>
                                                    <td><?= htmlspecialchars($user['email']); ?></td>
                                                    <td><?= htmlspecialchars($user['role']); ?></td>
                                                    <td>
                                                        <a href="userEdit.php?update=true&id=<?= urlencode($user['id']) ?>" class="btn btn-warning btn-sm">แก้ไข</a>
                                                        <button data-id="<?= $user['id']; ?>" class="btn btn-danger btn-sm deleteBtn">ลบ</button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="6" class="text-center">ไม่มีข้อมูลพนักงาน</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <?php include_once '../layouts/footer.php'; ?>
        </div>
    </div>

    <?php include_once '../layouts/config/script.php'; ?>
</body>


<script>
    $(document).ready(function() {
        $('.deleteBtn').click(function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'คุณแน่ใจหรือไม่?',
                text: "ข้อมูลนี้จะไม่สามารถกู้คืนได้!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ใช่, ลบเลย!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: "../../api/userApi.php",
                        data: {
                            action: 'delete',
                            id: id
                        },
                        success: function(response) {
                            try {
                                const res = JSON.parse(response);
                                if (res.status === 'success') {
                                    Swal.fire(
                                        'ลบแล้ว!',
                                        res.message,
                                        'success'
                                    ).then(() => location.reload());
                                } else {
                                    Swal.fire('ผิดพลาด', res.message, 'error');
                                }
                            } catch (e) {
                                Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการประมวลผล', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้', 'error');
                        }
                    });
                }
            });
        });
    });
</script>

</html>