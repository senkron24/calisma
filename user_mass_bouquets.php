<?php
include "session.php";
include "functions.php";
if (!$rPermissions["is_admin"] || !hasPermissions("adv", "mass_edit_users")) {
    exit;
}
if (isset($_POST["submit_user"])) {
    $_STATUS = 0;
    function recursive_array_replace ($find, $replace, $array) {
        if (!is_array($array)) {
            return str_replace($find, $replace, $array);
        }
    
        $newArray = [];
        foreach ($array as $key => $value) {
            $newArray[$key] = recursive_array_replace($find, $replace, $value);
        }
        return $newArray;
    }
    
    if (isset($_POST["all_users_selected"])) {
        $bouquetRes = json_decode($_POST["bouquets_selected"], True);
        unset($_POST["bouquets_selected"]);
    }
    $AddOrRemove = "Add";
    if (isset($_POST['removefrom'])) {
        $AddOrRemove = json_decode($_POST["removefrom"], True);  
        unset($_POST["removefrom"]); 
    } 
    $rUserSel = json_decode($_REQUEST['users_selected'], true);
    unset($_REQUEST['users_selected']);
    $_RESULTS = "";
    $rowCount = 0;

    if ($AddOrRemove == "Add") {
        foreach ($bouquetRes as $bouquet) { 
            $queryitem = "SELECT * FROM users WHERE !FIND_IN_SET(" . $bouquet . ", SUBSTRING(`bouquet`,2,length(bouquet)-2))";
            if ($_POST["all_users_selected"] == "false") 
            {
                $queryitem = "SELECT * FROM users WHERE !FIND_IN_SET(" . $bouquet . ", SUBSTRING(`bouquet`,2,length(bouquet)-2)) AND id IN(" .implode(',',$rUserSel). ")";
            }

            $result = $db->query($queryitem);
            $rowCount = mysqli_num_rows($result);

            if($rowCount > 0)
            {
                $count = 0;
                while ($row = mysqli_fetch_array($result))
                {
                    $bouquets = $row["bouquet"];
                    $bouquets = substr($bouquets, 0, strlen($bouquets)-1);
                    $str = $bouquets == "[" ? $bouquets . $bouquet . "]" : $bouquets .",". $bouquet. "]";
                        
                    $qry= "UPDATE users SET bouquet = '$str' WHERE id=".$row['id'];
                    $result2 = $db->query($qry);
                    if ($result2)
                        $count++;
                    else
                    {
                        $_RESULTS = "Error\n";
                        $_STATUS = 1;
                    }
                            
                } 
                $_RESULTS .= "<p>ID." . $bouquet . " Bouquet was added to " . $count . " user.</p>";
                //$_RESULTS = strlen($_RESULTS)>1 ? substr($_RESULTS,0,strlen($_RESULTS)-1) :$_RESULTS;
            }
    
        }
    }

    if ($AddOrRemove == "Remove") {
        foreach ($bouquetRes as $bouquet) {
            $queryitem = "SELECT * FROM users "; 
            if ($_POST["all_users_selected"] == "false") {
                $queryitem = "SELECT * FROM users WHERE id IN(" . implode(',', $rUserSel) . ")";
            }

            if ($result = $db->query($queryitem)) {
                $rowCount = mysqli_num_rows($result);
                if ($rowCount > 0) {
                    $count = 0;
                    while ($row = mysqli_fetch_array($result)) {
                        $str = "[";
                        $bouquets = $row["bouquet"];
                        $bouquets = substr($bouquets, 1, strlen($bouquets) - 2);
                        $katArray = explode(",", $bouquets);
                        $bouquetsDiff = array_diff($katArray, array($bouquet));
                        if($bouquets != $bouquetsDiff)
                        {
                            foreach ($bouquetsDiff as $kat) {
                                $str .= $kat . ",";
                            }
                            $str = substr($str, 0, strlen($str) - 1) . "]";

                            $qry = "UPDATE users SET bouquet = '$str' WHERE id=" . $row['id'];
                            $result2 = $db->query($qry);
                            if ($result2)
                                $count++;
                            else
                            {
                                $_RESULTS = "Error\n";
                                $_STATUS = 1;
                            }
                        }
                    }
                    $_RESULTS .= "<p>ID." . $bouquet . " Bouquet was removed to " . $count . " user.</p>";
                    //$_RESULTS = strlen($_RESULTS)>1 ? substr($_RESULTS,0,strlen($_RESULTS)-1) :$_RESULTS;
                } 
            }
        }
    }
    if($_RESULTS == "")
        $_STATUS = 0;

    unset($_POST["submit_user"]);
}

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
        if ($rSettings["sidebar"]) { ?>
        <div class="content-page"><div class="content boxed-layout-ext"><div class="container-fluid">
        <?php } else { ?>
        <div class="wrapper boxed-layout-ext"><div class="container-fluid">
        <?php } ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <a href="./users.php"><li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> <?=$_["back_to_users"]?></li></a>
                                </ol>
                            </div>
                            <h4 class="page-title">Mass Add/Remove User's Bouquets <small id="selected_count"></small></h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-xl-12">
                    <div class="message">
                        <?php if ((isset($_STATUS)) && ($_STATUS == 0) && $_RESULTS) { if (!$rSettings["sucessedit"]) { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?=$_RESULTS;?>
                        </div>
						<?php } else { ?>
                    <script type="text/javascript">
  					swal("", '<?=$_["mass_edit_of_users"]?>', "success");
  					</script>
                        <?php } } else if ((isset($_STATUS)) && ($_STATUS > 0)) { if (!$rSettings["sucessedit"]) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?=$_["there_was_an_error"]?>
                        </div>
						<?php } else { ?>
                    <script type="text/javascript">
  					swal("", '<?=$_["there_was_an_error"]?>', "warning");
  					</script>
                        <?php } } ?>
                    </div>
                        <div class="card">
                            <div class="card-body">
                                <form action="./user_mass_bouquets.php" method="POST" id="mass_user_form">
                                    <input type="hidden" name="users_selected" id="users_selected" value="" />
                                    <input type="hidden" name="bouquets_selected" id="bouquets_selected" value="" />
                                    <input type="hidden" name="all_users_selected" id="all_users_selected" value="" />
                                    <input type="hidden" name="removefrom" id="removefrom" value="" />
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#user-selection" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-group mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["user"]?></span>
                                                </a>
                                            </li>
                                            
                                            <li class="nav-item">
                                                <a href="#bouquets" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-flower-tulip mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["bouquets"]?></span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="user-selection">
                                                <div class="row">
                                                    <div class="col-md-3 col-6">
                                                        <input type="text" class="form-control" id="user_search" value="" placeholder="<?=$_["search_users"]?>">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <select id="reseller_search" class="form-control" data-toggle="select2">
                                                            <option value="" selected><?=$_["all_resellers"]?></option>
                                                            <?php foreach (getRegisteredUsers() as $rRegisteredUser) { ?>
                                                            <option value="<?=$rRegisteredUser["id"]?>"><?=$rRegisteredUser["username"]?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <select id="bouquets_search" class="form-control" data-toggle="select2">
                                                          <option value="" selected><?=$_["all_bouquets"]?></option>
                                                          <?php foreach (getBouquets() as $rBouquet) { ?>
                                                          <option value="<?=$rBouquet["id"]?>"><?=$rBouquet["bouquet_name"]?></option>
                                                          <?php } ?>
                                                      </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <select id="filter" class="form-control" data-toggle="select2">
                                                            <option value="" selected><?=$_["no_filter"]?></option>
                                                            <option value="1"><?=$_["active"]?></option>
                                                            <option value="2"><?=$_["disabled"]?></option>
                                                            <option value="3"><?=$_["banned"]?></option>
                                                            <option value="4"><?=$_["expired"]?></option>
                                                            <option value="5"><?=$_["trial"]?></option>
															<option value="6"><?=$_["mag_device"]?></option>
															<option value="7"><?=$_["enigma_device"]?></option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 col-8">
                                                        <select id="show_entries" class="form-control" data-toggle="select2">
                                                            <?php foreach (Array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                            <option<?php if ($rAdminSettings["default_entries"] == $rShow) { echo " selected"; } ?> value="<?=$rShow?>"><?=$rShow?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-1 col-2">
                                                        <button type="button" class="btn btn-info waves-effect waves-light" onClick="toggleUsers()">
                                                            <i class="mdi mdi-selection"></i>
                                                        </button>
                                                    </div>
                                                    <table id="datatable-mass" class="table table-hover table-borderless mb-0">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th class="text-center"><?=$_["id"]?></th>
                                                                <th><?=$_["username"]?></th>
                                                                <th></th>
                                                                <th><?=$_["reseller"]?></th>
                                                                <th class="text-center"><?=$_["status"]?></th>
                                                                <th class="text-center"><?=$_["online"]?></th>
                                                                <th class="text-center"><?=$_["trial"]?></th>
                                                                <th class="text-center"><?=$_["expiration"]?></th>
                                                                <th></th>
                                                                <th class="text-center"><?=$_["conns"]?></th>
                                                                <th></th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            
                                            <div class="tab-pane" id="bouquets">
                                                <div class="row">
                                                    <div class="col-12">
                                                    <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["prev"]?></a>
                                                    </li>
                                                    
                                                    <li class="next list-inline-item float-right">
                                                        <input type="checkbox" style="vertical-align: middle;" id="all_users" data-name="bouquets" data-type="bouquet" name="all_users">
                                                            <label style="color: #fff;background-color: #23b397; padding: 0.1rem 0.2rem 0.1rem 0.2rem;" for="all_users">Apply to All Users</label>
                                                        <input type="checkbox" style="vertical-align: middle;" id="remove_from_users" data-name="bouquets" data-type="bouquet" name="remove_from_users">
                                                            <label style="color: #fff;background-color: red; padding: 0.1rem 0.2rem 0.1rem 0.2rem;" for="remove_from_users"> Mass Remove </label>
                                                        <a href="javascript: void(0);" onClick="toggleBouquets()" class="btn btn-info"><?=$_["toggle_bouquets"]?></a>
                                                        <input name="submit_user" id="submit_user" type="submit" class="btn btn-primary" value="Mass Add" />                                                       
                                                    </li>

                                                </ul>
                                                        <div class="form-group row mb-4">
                                                            <table id="datatable-bouquets" class="table table-borderless mb-0">
                                                                <thead class="bg-light">
                                                                    <tr>
                                                                        <th class="text-center"><?=$_["id"]?></th>
                                                                        <th><?=$_["bouquet_name"]?></th>
                                                                        <th class="text-center"><?=$_["streams"]?></th>
                                                                        <th class="text-center"><?=$_["series"]?></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach (getBouquets() as $rBouquet) { ?>
                                                                    <tr>
                                                                        <td class="text-center"><?=$rBouquet["id"]?></td>
                                                                        <td><?=$rBouquet["bouquet_name"]?></td>
                                                                        <td class="text-center"><?=count(json_decode($rBouquet["bouquet_channels"], True))?></td>
                                                                        <td class="text-center"><?=count(json_decode($rBouquet["bouquet_series"], True))?></td>
                                                                    </tr>
                                                                    <?php } ?>
                                                                </tbody>
                                                            </table>
                                                            <!--<div class="custom-control col-md-12 custom-checkbox text-center" style="margin-top:20px;">
                                                                <input type="checkbox" class="custom-control-input" id="all_users" data-name="bouquets" data-type="bouquet" name="all_users">
                                                                <label class="custom-control-label" for="all_users">Tick this box to apply the above bouquets to all users.</label>
                                                            </div>-->
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                
                                            </div>
                                        </div> <!-- tab-content -->
                                    </div> <!-- end #basicwizard-->
                                </form>

                            </div> <!-- end card-body -->
                        </div> <!-- end card-->
                    </div> <!-- end col -->
                </div>
            </div> <!-- end container -->
        </div>
        <!-- end wrapper -->
        <?php if ($rSettings["sidebar"]) { echo "</div>"; } ?>

        <!-- Footer Start -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12 copyright text-center">Copyright © 2021 <?=htmlspecialchars($rSettings["server_name"])?></div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->

        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/libs/jquery-toast/jquery.toast.min.js"></script>
        <script src="assets/libs/jquery-ui/jquery-ui.min.js"></script>
        <script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
        <script src="assets/libs/switchery/switchery.min.js"></script>
        <script src="assets/libs/select2/select2.min.js"></script>
        <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
        <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
        <script src="assets/libs/datatables/jquery.dataTables.min.js"></script>
        <script src="assets/libs/datatables/dataTables.bootstrap4.js"></script>
        <script src="assets/libs/datatables/dataTables.responsive.min.js"></script>
        <script src="assets/libs/datatables/responsive.bootstrap4.min.js"></script>
        <script src="assets/libs/datatables/dataTables.buttons.min.js"></script>
        <script src="assets/libs/datatables/buttons.bootstrap4.min.js"></script>
        <script src="assets/libs/datatables/buttons.html5.min.js"></script>
        <script src="assets/libs/datatables/buttons.flash.min.js"></script>
        <script src="assets/libs/datatables/buttons.print.min.js"></script>
        <script src="assets/libs/datatables/dataTables.keyTable.min.js"></script>
        <script src="assets/libs/datatables/dataTables.select.min.js"></script>
        <script src="assets/libs/moment/moment.min.js"></script>
        <script src="assets/libs/daterangepicker/daterangepicker.js"></script>
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="assets/libs/treeview/jstree.min.js"></script>
        <script src="assets/js/pages/treeview.init.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
        var rSwitches = [];
        var rSelected = [];
        var rBouquets = [];

        function getReseller() {
            return $("#reseller_search").val();
        }
        function getFilter() {
            return $("#filter").val();
        }
        function toggleUsers() {
            $("#datatable-mass tr").each(function() {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selectedfilter').removeClass('ui-selected').removeClass("selected");
                    if ($(this).find("td:eq(0)").html()) {
                        window.rSelected.splice($.inArray($(this).find("td:eq(0)").html(), window.rSelected), 1);
                    }
                } else {            
                    $(this).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                    if ($(this).find("td:eq(0)").html()) {
                        window.rSelected.push($(this).find("td:eq(0)").html());
                    }
                }
            });
            $("#selected_count").html(" - " + window.rSelected.length + " selected")
        }
        function toggleBouquets() {
            $("#datatable-bouquets tr").each(function() {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selectedfilter').removeClass('ui-selected').removeClass("selected");
                    if ($(this).find("td:eq(0)").html()) {
                        window.rBouquets.splice($.inArray($(this).find("td:eq(0)").html(), window.rBouquets), 1);
                    }
                } else {            
                    $(this).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                    if ($(this).find("td:eq(0)").html()) {
                        window.rBouquets.push($(this).find("td:eq(0)").html());
                    }
                }
                if (!$("#all_users").is(":checked")) {
                    $("#all_users").prop('checked', true);
                }

            });
        }
        (function($) {
          $.fn.inputFilter = function(inputFilter) {
            return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
              if (inputFilter(this.value)) {
                this.oldValue = this.value;
                this.oldSelectionStart = this.selectionStart;
                this.oldSelectionEnd = this.selectionEnd;
              } else if (this.hasOwnProperty("oldValue")) {
                this.value = this.oldValue;
                this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
              }
            });
          };
        }(jQuery));
        $(document).ready(function() {
            $('select').select2({width: '100%'})
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            elems.forEach(function(html) {
                var switchery = new Switchery(html);
                window.rSwitches[$(html).attr("id")] = switchery;
            });
            $('#remove_from_users').click(function(){
                if ($("#remove_from_users").is(":checked")) {
                    $("#submit_user").val("Mass Remove");
                }
                else
                $("#submit_user").val("Mass Add");
            })
            
            $("#mass_user_form").submit(function(e){
                var rBouquets = [];
                $("#datatable-bouquets tr.selected").each(function() {
                    rBouquets.push($(this).find("td:eq(0)").html());
                });
                $("#bouquets_selected").val(JSON.stringify(rBouquets));

                if (!$("#all_users").is(":checked")) 
                    $("#all_users_selected").val(JSON.stringify(false));
                else
                    $("#all_users_selected").val(JSON.stringify(true));
                
                $("#users_selected").val(JSON.stringify(window.rSelected));
                if (window.rSelected.length == 0 && !$("#all_users").is(":checked")) {
                    e.preventDefault();
                    $.toast("<?=$_["select_at_least_one_user_to_edit"]?>");
                }
                
                if (!$("#remove_from_users").is(":checked")) 
                    $("#removefrom").val(JSON.stringify("Add"));
                else
                    $("#removefrom").val(JSON.stringify("Remove"));

            });
            
            $(window).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
            });
            $("#probesize_ondemand").inputFilter(function(value) { return /^\d*$/.test(value); });
          $("form").attr('autocomplete', 'off');
            rTable = $("#datatable-mass").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: "./table_search.php",
                    "data": function(d) {
                        d.id = "users",
                        d.filter = getFilter(),
                        d.reseller = getReseller(),
						d.showall = true
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [0,4,6,7,9]},
                    {"visible": false, "targets": [2,5,8,10,11]}
                ],
                "rowCallback": function(row, data) {
                    if ($.inArray(data[0], window.rSelected) !== -1) {
                        $(row).addClass("selected");
                    }
                },
                pageLength: <?=$rAdminSettings["default_entries"] ?: 10?>
            });
            bTable = $("#datatable-bouquets").DataTable({
                columnDefs: [
                    {"className": "dt-center", "targets": [0,2,3]}
                ],
                "rowCallback": function(row, data) {
                    if ($.inArray(data[0], window.rBouquets) !== -1) {
                        $(row).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                    }
                },
                paging: false,
                bInfo: false,
                searching: false
            });
            $('#user_search').keyup(function(){
                rTable.search($(this).val()).draw();
            })
            $('#show_entries').change(function(){
                rTable.page.len($(this).val()).draw();
            })
            $('#reseller_search').change(function(){
                rTable.ajax.reload(null, false);
            })
            $('#filter').change(function(){
                rTable.ajax.reload( null, false );
            })
            $("#datatable-mass").selectable({
                filter: 'tr',
                selected: function (event, ui) {
                    if ($(ui.selected).hasClass('selectedfilter')) {
                        $(ui.selected).removeClass('selectedfilter').removeClass('ui-selected').removeClass("selected");
                        window.rSelected.splice($.inArray($(ui.selected).find("td:eq(0)").html(), window.rSelected), 1);
                    } else {            
                        $(ui.selected).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                        window.rSelected.push($(ui.selected).find("td:eq(0)").html());
                    }
                    $("#selected_count").html(" - " + window.rSelected.length + " selected")
                }
            });
            $("#datatable-bouquets").selectable({
                filter: 'tr',
                selected: function (event, ui) {
                    if ($(ui.selected).hasClass('selectedfilter')) {
                        $(ui.selected).removeClass('selectedfilter').removeClass('ui-selected').removeClass("selected");
                        window.rBouquets.splice($.inArray($(ui.selected).find("td:eq(0)").html(), window.rBouquets), 1);
                    } else {            
                        $(ui.selected).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                        window.rBouquets.push($(ui.selected).find("td:eq(0)").html());
                    } 
                }
            });
            
        });
        
        </script>
        <?php
             $_POST["bouquets_selected"]=NIL;
        ?>
    </body>
</html>