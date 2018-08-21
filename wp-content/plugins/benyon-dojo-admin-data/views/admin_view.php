<script>
  <?php $admin_view_data = dojo_admin_get_data(); ?>
  var attendanceData = <?php echo json_encode($admin_view_data['attendanceData']); ?>;
  var attendanceTopScoresData = <?php echo json_encode($admin_view_data['attendanceTopScoresData']); ?>;
</script>

<div class="dojo-admin-view-container">
    <h1>Dojo Dashboard</h1>

  <div>
    <h2>Attendance Graph</h2>
    <canvas id="attendanceChart" width="400" height="150"></canvas>

    <h2>Most Attended Dojos</h2>
    <canvas id="attendanceTopScores" width="100" height="200"></canvas>

    <h2>Registered Members</h2>
    <canvas id="memberCounter" width="200" height="200"></canvas>
  </div>
</div>
