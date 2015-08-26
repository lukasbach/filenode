<?php
session_start();

$config = json_decode(file_get_contents("configuration.json"), true);

if(!isset($_SESSION["loggedIn"])) {
    $_SESSION["loggedIn"] = false;
}

?>

<!doctype html>

<html>
    <head>
        <title>filenode</title>
        
        <!-- Meta Data -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
                    
        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
        
        <!-- materializecss -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.0/css/materialize.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.0/js/materialize.min.js"></script>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
                
        <!-- Font Awesome files from bootstrap cdn -->
        <link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
            
        <!-- Uncompiled development less file -->
        <link rel="stylesheet" type="text/css" href="css/main.css" />

        <!-- highlight js -->
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.6/styles/default.min.css">
        <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.6/highlight.min.js"></script>
        <script>hljs.initHighlightingOnLoad();</script>
            
        <!-- Main script -->
        <script src="js/script.js"></script>

        <!-- logged in? -->
        <script>
        loggedIn = <?php echo $_SESSION['loggedIn'] ? "true" : "false"; ?>;
        config_imagepreview = <?php echo $config['imagepreview']; ?>;
        config_imagepreview_prerender_thumbnails = <?php echo $config['imagepreview_prerender_thumbnails']; ?>;
        colorscheme = "<?php echo $config['color_scheme']; ?>";
        basedir = "<?php echo $config['base_path']; ?>";

        <?php
        if(isset($_GET["loginSuccess"])) {
            ?>
            $(document).ready(function() {
                Materialize.toast('Login sucessfull.', 4000);
            });
            <?php
        } elseif(isset($_GET["settingssaved"])) {
            ?>
            $(document).ready(function() {
                Materialize.toast('Settings saved.', 4000);
            });
            <?php
        }

        if(isset($_GET["page"])) {
            ?>
            currentDir = "<?php echo $_GET["page"]; ?>";
            <?php
        } else {
            ?>
            currentDir = "";
            <?php
        }

        ?>
        </script>
    </head>

    <body>
        <header>
            <nav class="topnav color darken-1">
                <div class="row">
                    <div class="col s12 l6 offset-l3">
                        <div class="row">
                            <div class="col s5 m7 l7">
                                <div class="breadcrumbs truncate"></div>
                                <div class="headtitle">
                                    <!--<span class="color-text text-lighten-3">file</span><span>node</span>-->
                                    <span><?php echo $config["title"]; ?></span>
                                </div>
                            </div>
                            <div class="col s6 m5 l5">
                                <div class="nav-wrapper">
                                    <ul class="right topnav-icos">
                                        <li><a href="#!" onClick="showPathModal()"><i class="material-icons">content_copy</i></a></li>
                                        <li><a class="dropdown-button" href="#!" data-activates="nav-sort"><i class="material-icons">sort_by_alpha</i></a></li>
                                        <li><a class="dropdown-button" href="#!" data-activates="nav-moreoptions"><i class="material-icons">more_vert</i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <ul id="nav-moreoptions" class="nav-moreoptions dropdown-content">
                <li><a href="#!" class="color-text" onClick="openPage('.page.<?php echo $_SESSION['loggedIn'] ? "settings" : "login"; ?>')"><?php echo $_SESSION['loggedIn'] ? "settings" : "login"; ?></a></li>
                <?php if($_SESSION['loggedIn']) { ?><li><a href="#!" class="color-text" onClick="logout();">logout</a></li><?php } ?>
                <li class="divider"></li>
                <li><a href="#!" class="color-text" onClick="openDir(currentDir)">refresh</a></li>
                <li><a href="#!" class="color-text" onClick="$('#viewmodal').openModal()">view</a></li>
                <li class="divider"></li>
                <li><a href="#!" class="color-text">help</a></li>
                <li><a href="#!" class="color-text" onClick="openPage('.page.about')">about</a></li>
            </ul>

            <ul id="nav-sort" class="nav-moreoptions dropdown-content nav-sort">
                <li><a href="#!" class="color-text" onClick="sort('name', 'asc')">Name ascending</a></li>
                <li><a href="#!" class="color-text" onClick="sort('name', 'desc')">Name descending</a></a></li>
                <li class="divider"></li>
                <li><a href="#!" class="color-text" onClick="sort('filesize', 'asc')">Size ascending</a></li>
                <li><a href="#!" class="color-text" onClick="sort('filesize', 'desc')">Size descending</a></a></li>
                <li class="divider"></li>
                <li><a href="#!" class="color-text" onClick="sort('lastmodtimest', 'asc')">Last Edit ascending</a></li>
                <li><a href="#!" class="color-text" onClick="sort('lastmodtimest', 'desc')">Last Edit descending</a></a></li>
                <li class="divider"></li>
            </ul>
        </header>

        <form>
            <div class="row filetable-mastercontainer layout-default">
                <div class="col s12 l6 offset-l3 filetable-container"> <!-- m8 offset-m2 -->
                </div>
            </div>
        </form>

        <div class="pages hide">
            <div class="page about">
                <div class="title">
                    <span class="color-text text-darken-2">file</span><span class="grey-text text-darken-2">node</span>
                </div>
                <div class="info">
                    <p>Version 1.0</p>
                    <p>&copy; 2015 by <a href='http://lukasbach.com'>Lukas Bach</a></p>
                </div>
                <p class="center-align">
                    <button class="btn color waves-effect"><i class="material-icons left">code</i>Github page</button>
                    <button class="btn color waves-effect"><i class="material-icons left">cloud</i>filenode website</button>
                </p>
                <br /><br />
                <a class="waves-effect waves-light color lighten-2 pull-right btn" onClick="openDir(currentDir)"><i class="material-icons right">keyboard_backspace</i>back to filelist</a>
            </div>

            <div class="page textviewer">
                <a class="waves-effect waves-light color pull-right btn" onClick="openDir(currentDir)"><i class="material-icons right">keyboard_backspace</i>back to filelist</a>
                <h4 class="color-text textviewer-heading"></h4>
                <pre><code class="textviewer-code css"></code></pre>
            </div>

            <div class="page imageviewer">
                <a class="waves-effect waves-light color pull-right btn" onClick="openDir(currentDir)"><i class="material-icons right">keyboard_backspace</i>back to filelist</a>
                <h4 class="color-text imageviewer-heading"></h4>
                <div class="row">
                    <img class="col s12 imageviewer-imgcontainer" data-caption="" src="" onClick="" />
                </div>
            </div>

            <div class="page audioviewer">
                <a class="waves-effect waves-light color pull-right btn" onClick="openDir(currentDir)"><i class="material-icons right">keyboard_backspace</i>back to filelist</a>
                <h4 class="color-text audioviewer-heading"></h4>
                <center>
                    <audio src="" controls class="audioviewer-audiocontainer"></audio>
                </center>
            </div>

            <div class="page videoviewer">
                <a class="waves-effect waves-light color pull-right btn" onClick="openDir(currentDir)"><i class="material-icons right">keyboard_backspace</i>back to filelist</a>
                <h4 class="color-text videoviewer-heading"></h4>
                <center>
                    <video class="videoviewer-videocontainer" controls>
                        <source src="" type="" />
                    </video>
                </center>
            </div>

            <div class="page login">
                <a class="waves-effect waves-light color pull-right btn" onClick="openDir(currentDir)"><i class="material-icons right">keyboard_backspace</i>back to filelist</a>
                <h4 class="color-text">Log into admin panel</h4>

                <div class="row">
                    <form class="col s12">
                        <div class="row">
                            <div class="input-field col s6">
                                <i class="material-icons prefix">person</i>
                                <input id="user" type="text" class="validate userfield">
                                <label for="user">Username</label>
                            </div>
                            <div class="input-field col s6">
                                <i class="material-icons prefix">lock</i>
                                <input id="psw" type="password" class="validate pswfield">
                                <label for="psw">Password</label>
                            </div>
                        </div>
                        <a class="waves-effect waves-light btn color pull-right" onClick="login()"><i class="material-icons right">send</i>login</a>
                    </form>
                </div>
            </div>

            <div class="page settings">
                <a class="waves-effect waves-light color pull-right btn" onClick="openDir(currentDir)"><i class="material-icons right">keyboard_backspace</i>back to filelist</a>
                <h4 class="color-text">Settings</h4>
                <hr />

                <div class="row">
                    <form class="col l8 m12 s12 offset-l2">
                        <div class="row">
                            <div class="input-field col s12">
                                <input id="title" type="text" class="validate title" value="<?php echo $config['title']; ?>" />
                                <label for="title">Heading</label>
                            </div>
                            <div class="input-field col s12">
                                <input id="base_path" type="text" class="validate base_path" value="<?php echo $config['base_path']; ?>" />
                                <label for="base_path">Base path</label>
                            </div>
                            <div class="input-field col s12">
                                <p>Color scheme</p>

                                <div class="row">

                                    <?php
                                        $colors = array("red","pink","purple","deep-purple","indigo","blue","light-blue","cyan","teal","green","light-green","lime","yellow","amber","orange","deep-orange","brown","grey","blue-grey","black");
                                        
                                        foreach($colors AS $color) {
                                            ?>
                                            <div class="col s6" onClick="setColor('<?php echo $color; ?>')">
                                                <input name="select-color" class="select-color" type="radio" value="<?php echo $color; ?>" id="select-color-<?php echo $color; ?>" <?php if($color == $config["color_scheme"]) {echo "checked";} ?> />
                                                <label for="select-color-<?php echo $color; ?>"><?php echo $color; ?></label>
                                            </div>
                                            <?php
                                        }
                                    ?>

                                </div>
                            </div>
                            <div class="col s12">
                                <p>
                                    <input type="checkbox" id="imagepreview" class="imagepreview" <?php if($config["imagepreview"] == "true") {echo "checked";} ?> />
                                    <label for="imagepreview">Show image preview on large layout</label>
                                </p>
                            </div>
                            <div class="col s12">
                                <p>
                                    <input type="checkbox" id="imagepreview_prerender_thumbnails" class="imagepreview_prerender_thumbnails" <?php if($config["imagepreview_prerender_thumbnails"] == "true") {echo "checked";} ?> />
                                    <label for="imagepreview_prerender_thumbnails">Prerender and use thumbnails for image preview (thumbnails will be stored in <i>thumbnails/</i></label>
                                </p>
                            </div>
                            <div class="col s12">
                                <div class="input-field col s12">
                                  <textarea id="blacklist" class="materialize-textarea blacklist"><?php
                                    foreach($config["viewfiles_blacklist"] AS $listitem) {
                                        echo $listitem . "\n";
                                    }
                                  ?></textarea>
                                  <label for="blacklist">Blacklisted paths (one path per line)</label>
                                </div>
                            </div>
                            <div class="col s12">
                                <p>Enter new login credentials if you want to change them.</p>
                                <div class="row">
                                    <div class="input-field col s6">
                                        <i class="material-icons prefix">person</i>
                                        <input id="user" type="text" class="validate userfield">
                                        <label for="user">Username</label>
                                    </div>
                                    <div class="input-field col s6">
                                        <i class="material-icons prefix">lock</i>
                                        <input id="psw" type="password" class="validate pswfield">
                                        <label for="psw">Password</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col s12">
                                <a class="btn waves-effect waves-light color pull-right" onClick="saveSettings()"><i class="material-icons right">send</i> save changes</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="pathmodal" class="modal pathmodal">
            <div class="modal-content">
                <h5>Current path</h5>
                <pre class="pathmodal-currentpath"></pre>
                <h5>filenode url</h5>
                <pre class="pathmodal-url"></pre>
            </div>
            <div class="modal-footer">
                <a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat">close</a>
            </div>
        </div>

        <div id="fileinfomodal" class="modal bottom-sheet fileinfomodal">
            <div class="modal-content">
                <h4>Element information</h4>

                <ul class="collection">
                    <li class="collection-item openas-btn color-text">
                        <div>
                            Open as new tab
                            <a href="#!" class="secondary-content"><i class="material-icons color-text">send</i></a>
                        </div>
                    </li>
                    <li class="collection-item avatar">
                        <i class="material-icons circle color lighten-2">send</i>
                        <span class="title filename">Title</span>
                        <p>Filename</p>
                    </li>
                    <li class="collection-item avatar">
                        <i class="material-icons circle color lighten-2">send</i>
                        <span class="title filesize">Title</span>
                        <p>Filesize</p>
                    </li>
                    <li class="collection-item avatar">
                        <i class="material-icons circle color lighten-2">access_time</i>
                        <span class="title lastedited">Title</span>
                        <p>Last edited</p>
                    </li>
                    <li class="collection-item avatar">
                        <i class="material-icons circle color lighten-2">change_history</i>
                        <span class="title path">Title</span>
                        <p>Path to element</p>
                    </li>
                </ul>
            </div>
            <div class="modal-footer">
                <a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat">Close</a>
            </div>
        </div>

        <div id="viewmodal" class="modal bottom-sheet viewmodal">
            <div class="modal-content">
                <h4>Layout</h4>

                <div class="row">
                    <div class="col l4 offset-l4 m6 offset-m3 s12">
                        <div class="col s4 center-align waves-effect" onClick="changeView('list')">
                            <i class="medium material-icons">view_headline</i><br />
                            <b>List</b>
                        </div>
                        <div class="col s4 center-align waves-effect" onClick="changeView('default')">
                            <i class="medium material-icons">view_list</i><br />
                            <b>Default</b>
                        </div>
                        <div class="col s4 center-align waves-effect" onClick="changeView('large')">
                            <i class="medium material-icons">view_module</i><br />
                            <b>Large</b>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat">Close</a>
            </div>
        </div>

        <div id="sortmodal" class="modal bottom-sheet sortmodal">
            <div class="modal-content">
                <h4>Sort</h4>

                <div class="row">
                    <div class="col l6 offset-l3 m8 offset-m2 s12">
                        <div class="col s3 center-align">
                            <div>
                                <i class="medium material-icons">sort_by_alpha</i>
                            </div>
                            <div>
                                <i class="material-icons">keyboard_arrow_up</i><b>File name</b>
                            </div>
                            <div>
                                <i class="material-icons">keyboard_arrow_down</i><b>File name</b>
                            </div>
                        </div>
                        <div class="col s3 center-align" onClick="changeView('default')">
                            <i class="medium material-icons">format_size</i><br />
                            <b>Default</b>
                        </div>
                        <div class="col s3 center-align" onClick="changeView('large')">
                            <i class="medium material-icons">access_timer</i><br />
                            <b>Large</b>
                        </div>
                        <div class="col s3 center-align" onClick="changeView('large')">
                            <i class="medium material-icons">folder</i><br />
                            <b>Large</b>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat">Close</a>
            </div>
        </div>

        <div class="elementprototypes hide">
            <div class="filetable-default_">
                <table class="striped">
                    <thead class="z-depth-5">
                        <tr>
                            <td>Name</td>
                            <td>Last modified</td>
                            <td>Filesize</td>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <ul class="collection filetable-default">
                <li class="collection-item avatar filetable-element filetable-element-folderup">
                    <i class="material-icons circle color filetable-element-ico">folder</i>
                    <i class="material-icons color-text filetable-element-ico ico-alt">folder</i>
                    <span class="title filetable-element-filename">..</span>
                </li>

                <li class="collection-item avatar filetable-element-default">
                    <i class="material-icons circle color filetable-element-ico">folder</i>
                    <i class="material-icons color-text filetable-element-ico ico-alt">folder</i>
                    <span class="title filetable-element-filename"></span>
                    <p class="filetable-element-infotext"></p>
                    <a href="#!" class="secondary-content">
                      <input type="checkbox" id="" onClick="selectElement(this)" />
                      <label for=""></label>
                    </a>
                </li>
            </ul>

            <tr class="filetable-row-default">
                <td class="filetable-row-col-filename"></td>
                <td class="filetable-row-col-lastmodified"></td>
                <td class="filetable-row-col-filesize"></td>
            </tr>

            <div class="preloader-wrapper active preloader-default">
                <div class="spinner-layer spinner-red-only">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div><div class="gap-patch">
                        <div class="circle"></div>
                    </div><div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
            </div>
        </div>


        <script>
            openDir(currentDir);
        </script>
    </body>
</html>