      <div class="box">
        <div class="box-header" style="background-color: #fff;">
          <h4 class="text-black" style="text-align: center;padding: 15px;">
             <?=$this->lang->line("dashboard_teacher_today_attendance")?>
            </h4>
        </div>

        <div class="box-body" style="padding: 0px;">
          <table class="table table-hover">
              <tbody>
                <?php
                // print_r(count($teacherWiseAttendance));
                // exit();
              
                    $todaysAttendances = [];
            foreach ($teacherWiseAttendance as $key => $value) {
                $todaysAttendances[$key] = $value[(int)date('d')];
                        echo "<tr>";
                        echo "<td>";
                        ?>
                        Present:
                        <?php
                        echo "</td>";
                         echo "<td>";
                        echo($todaysAttendances[$key]['P']);
                        echo "</td>";
                        echo "<td>";
                        echo btn_dash_view('tattendance/add', $this->lang->line('view'), 'bg-maroon-light');
                        echo "</td>";
                        echo "</tr>";
                        echo "<tr>";
                        echo "<td>";
                        ?>  
                        Late Present:
                        <?php
                        echo "</td>";
                         echo "<td>";
                        echo($todaysAttendances[$key]['L']);
                        echo "</td>";
                         echo "<td>";
                        echo btn_dash_view('tattendance/add', $this->lang->line('view'), 'bg-maroon-light');
                        echo "</td>";
                        echo "</tr>";
                        echo "<tr>";
                        echo "<td>";
                        ?> 
                        Absent:
                        <?php
                        echo "</td>";
                         echo "<td>";
                        echo($todaysAttendances[$key]['A']);
                        echo "</td>";
                         echo "<td>";
                        echo btn_dash_view('tattendance/add', $this->lang->line('view'), 'bg-maroon-light');
                        echo "</td>";
                        echo "</tr>";

                    }
                 


                ?>
              </tbody>
          </table>
        </div>
      </div>