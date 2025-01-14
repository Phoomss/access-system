<!-- ปุ่มบันทึกเวลาเข้าออก -->
<div class="col-md-6 mb-2" id="model">
    <a role="button" class="btn btn-outline-danger w-100" type="button" data-bs-toggle="modal" data-bs-target="#departureModel" id="departureButton">บันทึกเวลาออกงาน</a>
</div>
<!-- Modal for Attendance -->
<div class="modal fade" id="departureModel" tabindex="-1" aria-labelledby="departureModelLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="departureModelLabel">บันทึกเวลาเข้าออก</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="attendanceForm">
                    <!-- ชื่อ-นามสกุล -->
                    <div class="mb-3">
                        <label for="employee_name" class="form-label">ชื่อ-นามสกุล</label>
                        <input type="hidden" id="employee_id" name="employee_id" value="<?php echo htmlentities($userData['id']); ?>">
                        <input type="text" class="form-control" id="employee_name" name="employee_name"
                            value="<?php echo htmlentities($userData['title'] . ' ' . $userData['firstname'] . ' ' . $userData['surname']); ?>"
                            readonly>
                    </div>
                    <!-- ช่องเลือกวันที่ -->
                    <div class="mb-3">
                        <label for="attendance_date" class="form-label">วันที่</label>
                        <input type="date" class="form-control" id="attendance_date" name="attendance_date" required>
                    </div>
                    <!-- เวลาเข้า -->
                    <div class="mb-3">
                        <label for="attendance_time" class="form-label">เวลาเข้า</label>
                        <input type="time" class="form-control" id="attendance_time" name="attendance_time" required>
                    </div>
                    <div class="mb-3">
                        <label for="departure_time" class="form-label">เวลาออก</label>
                        <input type="time" class="form-control" id="departure_time" name="departure_time" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-primary" id="saveAttendanceBtn">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<script>
    // ฟังก์ชันแปลงเวลาเป็นรูปแบบภาษาไทย
    function getThaiTimeString(date) {
        const options = {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }; // รูปแบบ 24 ชั่วโมง
        return date.toLocaleTimeString('th-TH', options);
    }

    // ฟังก์ชันตรวจสอบเวลา
    function checkTimeToShowButton() {
        const currentTime = new Date();

        // ถ้าเวลามากกว่าหรือเท่ากับ 12:00 แสดงปุ่ม
        if (currentTime.getHours() >= 12) {
            document.getElementById('departureButton').style.display = 'block';
        } else {
            document.getElementById('departureButton').style.display = 'none';
        }
    }

    window.onload = checkTimeToShowButton;

    $(document).ready(function() {
        $('#departureButton').click(function() {
            const employeeId = $('#employee_id').val();
            console.log("Employee ID:", employeeId);

            $.ajax({
                url: `http://127.0.0.1/attendance-system/api/attendanceApi.php?action=getLatest&id=${employeeId}`,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log("Raw API response:", JSON.stringify(data));

                    if (data.success && data.data) {
                        const fetchData = data.data;
                        console.log("Processed data:", fetchData);

                        // กำหนดค่าฟอร์มเมื่อได้ข้อมูลจาก API
                        $('#attendance_date').val(formatDate(fetchData.attendance_date));
                        $('#attendance_time').val(formatTime(fetchData.attendance_time));
                        $('#departure_time').val(formatTime(fetchData.departure_time));
                    } else {
                        console.log("No data or error:", data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        });

        // ฟังก์ชันแปลงรูปแบบวันที่
        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toISOString().split('T')[0]; // คืนค่าในรูปแบบ YYYY-MM-DD
        }

        // ฟังก์ชันแปลงรูปแบบเวลา
        function formatTime(timeStr) {
            return timeStr ? timeStr.substring(0, 5) : ''; // คืนค่าในรูปแบบ HH:MM
        }
    });


    // update form
    jQuery(document).ready(function($) {
        $('#saveAttendanceBtn').on('click', function(e) {
            e.preventDefault();

            // Validate form
            if (!$('#attendance_date').val() || !$('#attendance_time').val() || !$('#departure_time').val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                    confirmButtonText: 'ตกลง',
                });
                return;
            }

            var formData = {
                action: 'create',
                employee_id: $('#employee_id').val(),
                attendance_date: $('#attendance_date').val(),
                attendance_time: $('#attendance_time').val(),
                departure_time: $('#departure_time').val(),
            };

            // ส่งข้อมูลไปยัง API
            $.ajax({
                type: "POST",
                url: "../../api/attendanceApi.php",
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ',
                            text: response.message,
                            confirmButtonText: 'ตกลง',
                        }).then(() => {
                            // Reset form and close modal
                            $('#attendanceForm')[0].reset();
                            $('#departureModel').modal('hide');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'ข้อผิดพลาด',
                            text: response.message,
                            confirmButtonText: 'ตกลง',
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'ข้อผิดพลาด',
                        text: 'เกิดข้อผิดพลาดในการส่งข้อมูล',
                        confirmButtonText: 'ตกลง',
                    });
                },
            });
        });
    });
</script>