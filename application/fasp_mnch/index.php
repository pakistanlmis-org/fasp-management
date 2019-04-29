<?php
/**
 * index
 * @package fasp
 * 
 * @author     Ajmal Hussain
 * @email <ahussain@ghsc-psm.org>
 * 
 * @version    2.2
 * 
 */
//include AllClasses
include("../includes/classes/AllClasses.php");
//include header
include(PUBLIC_PATH . "html/header.php");

?>
<link href="demo.css" rel="stylesheet">
<link href="introjs.css" rel="stylesheet">
</head>
<!-- END HEAD -->
<body class="page-header-fixed page-quick-sidebar-over-content">
    <div class="modal"></div>
    <div class="page-container">
        <?php
        //include top
        include PUBLIC_PATH . "html/top.php";
        //include top_im	
        include PUBLIC_PATH . "html/top_im.php";
        ?>
        <div class="page-content-wrapper">
            <div class="page-content">
                <div class="row well well-dark">
                   <div class="portlet   dark center">
                        <h1 >Forecasting & Quantification</h1> <a id="about_btn" class=" btn btn-large btn-warning" href="javascript:void(0);" onclick="javascript:introJs().start();">Show me what is this module about <i style="font-size: 24px" class="fa fa-question"></i></a>
                        
                        <h3 >MNCH Products</h3> 
                        <div ><a href="indicator_details.php">( Indicator Details )</a></div> 

                   </div>
                </div> 
                 
                
                <div data-step="1" data-intro="<b>What is Forecasting:</b> Forecasting is the process of making predictions of the future based on past and present data .<br/><br/>Forecasting module for MNCH products helps you to calculate the forecasted quantities based on different parameters.<br/><br/>Click <b>Next</b> to view different steps of forecasting/quantification."  class="row well " id="mnch_div" style=" ">
                    <div class="col-md-12">
                        <div class="col-md-4">
                    <div   class="tiles">
                        
                        
                                      <div  data-step="2" data-intro="Enter year wise demographics data, which will be used in the forecasts."   class="tile double bg-green">
                                          <a href="demographics_list.php">
                                              <div class="tile-body">
                                                      <i class="fa fa-bell-o1"><img width="120px" src="images/dmg.png"></i>
                                              </div>
                                              <div class="tile-object">
                                                      <div class="name">
                                                               Demographics
                                                      </div>
                                                      <div class="number">

                                                      </div>
                                              </div>
                                          </a>
                                      </div>
                        
                                    <div  data-step="3" data-intro="Base Settings are mandatory configurations for this module, where you do configurations for clinical conditions."  class="tile double bg-red-sunglo">
                                        <a href="product_settings.php?product_category=mnch">
                                              <div class="tile-body">
                                                      <i class="fa fa-cogs1"><img width="80px" src="images/settings.png"></i>
                                              </div>
                                              <div class="tile-object">
                                                      <div class="name">
                                                               Base Settings
                                                      </div>
                                                      <div class="number">

                                                      </div>
                                              </div>
                                          </a>
                                      </div>
                                      <div  data-step="4" data-intro="Create New Forecast"  class="tile double bg-yellow-crusta">
                                          <a href="forecasting_master.php?product_category=mnch">
                                              <div class="tile-body">
                                                      <i class="fa fa-plus1"><img width="70px" src="images/add.png"></i>
                                              </div>
                                              <div class="tile-object">
                                                      <div class="name">
                                                               New Forecast
                                                      </div>
                                                      <div class="number">

                                                      </div>
                                              </div>
                                          </a>
                                      </div>
                        
                                      <div  data-step="5" data-intro="View Forecast List."   class="tile double bg-green">
                                          <a href="forecasting_list.php">
                                              <div class="tile-body">
                                                      <i class="fa fa-list1"><img width="70px" src="images/list.png"></i>
                                              </div>
                                              <div class="tile-object">
                                                      <div class="name">
                                                               Forecast List
                                                      </div>
                                                      <div class="number">

                                                      </div>
                                              </div>
                                          </a>
                                      </div>

                              </div></div>
                        <div class="col-md-2"><img style="padding-top:50px" src="brackets.png"></div>
                        <div class="col-md-4" style="padding-top:70px">
                            <div class="tiles">

                                              <div  data-step="6"    data-intro="Forecasting form will be displaying the Demographic , Morbidity Data , calculated based on rates/percentages/configurations provided of  the product / disease ." class="tile double-down  bg-yellow-lemon">
                                                  <a href="forecasting_list.php">
                                                      <div class="tile-body"><i class="fa fa-bar-chart-o1"><img width="120px" src="images/fc.png"></i>
                                                      </div>
                                                      <div class="tile-object">
                                                              <div class="name">
                                                                       MNCH Products Forecasting Calculations
                                                              </div>
                                                              <div class="number">

                                                              </div>
                                                      </div>
                                                  </a>
                                              </div>
                                              <div  data-step="7"  data-intro="Quantification Form fetches the proposed forecasting result. Its used for quantifying against the prices of units." class="tile double-down bg-blue-madison">
                                                  <a href="forecasting_list.php">
                                                      <div class="tile-body">
                                                          <i class="fa fa-flask1"><img width="100px" src="images/qnt.png"></i>
                                                      </div>
                                                      <div class="tile-object">
                                                              <div class="name">
                                                                       Quantification
                                                              </div>
                                                              <div class="number">

                                                              </div>
                                                      </div>
                                                  </a>
                                              </div>

                            </div>
                                
                        </div>
                        
                        
                    </div>
                </div>
                
                
                
            </div>
        </div>
    </div>
    

    <script src="../../public/js/jquery-1.4.4.js" type="text/javascript"></script>

    <script type="text/javascript" src="intro.js"></script>
     <?php 
    //include footer
    include PUBLIC_PATH . "/html/footer.php"; 
    ?>
    <script>
        $('#mnch_btn').click(function(){
            var x = document.getElementById("mnch_div");
            x.style.display = "block";
            document.getElementById("fp_div").style.display = "none";
            $(this).removeClass("btn-default").addClass("btn-success");
            $('#fp_btn').removeClass("btn-success").addClass("btn-default");
            $('#about_btn').hide();
        });
        $('#fp_btn').click(function(){
            var x = document.getElementById("fp_div");
            x.style.display = "block";
            document.getElementById("mnch_div").style.display = "none";
            $(this).removeClass("btn-default").addClass("btn-success");
            $('#mnch_btn').removeClass("btn-success").addClass("btn-default");
            $('#about_btn').show();
        });
    </script>
</body>
</html>