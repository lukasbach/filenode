//currentDir = "";
sortfor = "name";
sortdirection = "desc";
//colorscheme = "";

$(document).ready(function() {
    setTitle();
    setColor(colorscheme);
    $('select').material_select();
});

function openDir(newdir) {
    //newdir = correctPath(newdir);

    $.ajax({
        url: "serverinterface.php?getDirectoryContent&path=" + newdir + "&sortfor=" + sortfor + "&sortdirection=" + sortdirection,
        dataType: "json",
        success: function(dir) {
            console.log(dir);

            if("error" in dir) {
                error(dir["error"]);
            } else {
                currentDir = newdir.charAt(0) == "/" ? newdir.substring(1) : newdir;

                document.title = currentDir == "" ? "filenode" : currentDir;
                window.history.pushState(
                    {
                        "pageTitle":currentDir
                    },
                    "",
                    "main.php?page=" + currentDir
                );

                updateBreadcrumbs();

                $container = $(".filetable-container");
                $filetable_default = $(".filetable-default");

                $container.html("");

                $filetable = $filetable_default.clone();

                $filetable
                    .removeClass("filetable-default")
                    .addClass("filetable");

                $.each(dir, function(i, element) {
                    fileextension = element["isdir"] ? "folder" : element["name"].split(".")[element["name"].split(".").length - 1];

                    $row = $filetable
                        .find(".filetable-element-default")
                        .clone()
                        .removeClass("filetable-element-default")
                        .addClass("filetable-element")
                        .attr("data-path", currentDir + "/" + element["name"])
                        .attr("data-name", element["name"])
                        .attr("data-filesize", element["filesize"])
                        .attr("data-lastmod", element["lastmod"])
                        .attr("data-isdir", element["isdir"]);

                    if($.inArray(fileextension, previewExtensions) != -1 && config_imagepreview) {
                        if(config_imagepreview_prerender_thumbnails) {
                            $.ajax({
                                url: "serverinterface.php?imgthumbnail&path=" + basedir + "/" + currentDir + "/" + element["name"],
                                dataType: "html",
                                success: function(thumbnailpath) {
                                    $("[data-path='" + currentDir + "/" + element["name"] + "']").css("background-image", "url(" + thumbnailpath + ")");
                                }
                            });
                        } else {
                            $row.css("background-image", "url(" + basedir + "/" + currentDir + "/" + element["name"] + ")");
                        }
                    }

                    $row.find(".filetable-element-filename").html(element["name"]);
                    $row.find(".filetable-element-infotext").html(element["isdir"] ? "Folder" : "Filesize: " + filesizeRedo(element["filesize"]) + "<br />" + "Last modified: " + element["lastmod"]);
                    $row.find(".filetable-element-ico").html(fileextension in filelisticons ? filelisticons[fileextension] : filelisticons["default"]);
                    $row.find(".filetable-element-ico").addClass(element["isdir"] ? "darken-2" : "lighten-2");

                    $row.find("input").attr("id", "chckel" + i);
                    $row.find("label").attr("for", "chckel" + i);

                    $row.click(function(event) {
                        if(event.target.nodeName != "LABEL" && event.target.nodeName != "INPUT") {
                            //element = $(event.target).parents(".filetable-element");
                            fileextension = element["isdir"] ? "folder" : element["name"].split(".")[element["name"].split(".").length - 1];

                            if(element["isdir"]) {
                                openDir(currentDir + (currentDir != "" ? "/" : "") + element["name"]);
                            } else {
                                openViewer(fileextension in fileviewertypes ? fileviewertypes[fileextension][0] : fileviewertypes["default"], basedir + "/" + currentDir + "/" + element["name"]);
                            }
                        }
                    });

                    $row.appendTo($filetable);
                });

                $filetable.find(".filetable-element-folderup").click(function() {
                        folderUp();
                });

                $filetable.find(".filetable-element-default").remove();

                $container.append($filetable);
            }
        }
    });
}


function openPage(selector) {
    $(".filetable-container").html("");
    $(selector).clone().appendTo($(".filetable-container"));
}


function updateBreadcrumbs() {
    $container = $(".breadcrumbs");

    $container.html("");

    pathToThis = "";
    baseDirSplit = basedir.split("/");

    // root
    $("<div></div>")
        .addClass("breadcrumbs-folder")
        .addClass(colorscheme + "-text text-lighten-3 color-text")
        .html("root")
        .attr("onClick", "openDir('')")
        .appendTo($container);

    $("<div></div>")
        .addClass("breadcrumbs-slash")
        .addClass(colorscheme + "-text text-lighten-3 color-text")
        .html("/")
        .appendTo($container);

    // every other folder
    $.each(currentDir.split("/"), function(i, folder) {
        if(i != 0) {
            pathToThis += "/";
        }
        pathToThis += folder;

        if(folder == baseDirSplit[i]) {
            return true;
        }

        $("<div></div>")
            .addClass("breadcrumbs-folder")
            .addClass(colorscheme + "-text text-lighten-3 color-text")
            .html(folder)
            .attr("onClick", "openDir('" + pathToThis + "')")
            .appendTo($container);

        $("<div></div>")
            .addClass("breadcrumbs-slash")
            .addClass(colorscheme + "-text text-lighten-3 color-text")
            .html("/")
            .appendTo($container);
    });

    if(/*currentDir.split("/").length == 1*/ currentDir == "") {
        $(".headtitle").removeClass("hide");
        $(".breadcrumbs").addClass("hide");
    } else {
        $(".headtitle").addClass("hide");
        $(".breadcrumbs").removeClass("hide");
    }
}


function folderUp() {
    pathSplitted = currentDir.split("/");
    if(/*pathSplitted.length == 1*/currentDir == "") {
        alert("Can't go up any more.");
    } else {
        delete pathSplitted[pathSplitted.length - 1];
        currentDir = pathSplitted.join("/").substring(0, pathSplitted.join("/").length - 1);
        openDir(currentDir);
    }
}


function sort(newsortfor, newsortdirection) {
    sortfor = newsortfor;
    sortdirection = newsortdirection;
    openDir(currentDir);
}


function selectElement(el) {
    $eventSource = $(el).parents(".filetable-element");

    $eventSource.addClass("keepBox");

    filename = $eventSource.attr("data-name");
    filesize = $eventSource.attr("data-filesize");
    path = $eventSource.attr("data-path");
    lastmod = $eventSource.attr("data-lastmod");
    isdir = $eventSource.attr("data-isdir");

    $('.fileinfomodal').find(".filename").html(filename);
    $('.fileinfomodal').find(".filesize").html(filesizeRedo(filesize));
    $('.fileinfomodal').find(".path").html(path);
    $('.fileinfomodal').find(".lastedited").html(lastmod);

    $(".openas-btn").remove();

    if(isdir == "true") {
        $('.fileinfomodal').find(".filesize").parent().css("display", "none");
        $('.fileinfomodal').find(".lastedited").parent().css("display", "none");
        $("<li></li>")
            .addClass("collection-item openas-btn " + colorscheme + "-text color-text")
            .html("<div>Open folder<a href='#!' class='secondary-content'><i class='material-icons " + colorscheme + "-text color-text'>send</i></a></div>")
            .attr("onClick", "openDir('" + path + "');$('#fileinfomodal').closeModal()")
            .prependTo($(".fileinfomodal .collection"));
    } else {
        $('.fileinfomodal').find(".filesize").parent().css("display", "block");
        $('.fileinfomodal').find(".lastedited").parent().css("display", "block");
        fileextension = filename.split(".")[filename.split(".").length - 1];

        $.each(fileextension in fileviewertypes ? fileviewertypes[fileextension] : fileviewertypes["default"], function(i, viewer) {
            $("<li></li>")
                .addClass("collection-item openas-btn " + colorscheme + "-text color-text")
                .html("<div>" + fileviewertypes_text[viewer] + "<a href='#!' class='secondary-content'><i class='material-icons " + colorscheme + "-text color-text'>send</i></a></div>")
                .attr("onClick", "openViewer('" + viewer + "', '" + basedir + "/" + path + "');$('#fileinfomodal').closeModal()")
                .prependTo($(".fileinfomodal .collection"));
        });
    }

    $('#fileinfomodal').openModal({
        complete: function() { 
            $(el).attr('checked', false);
            $(".keepBox").removeClass("keepBox");
        }
    });
}


function openViewer(filetype, path) {
    switch(filetype) {
        case "text":
            openPage(".page.textviewer");
            textViewer(path);
            break;
        case "image":
            openPage(".page.imageviewer");
            imageviewer(path);
            break;
        case "audio":
            openPage(".page.audioviewer");
            audioviewer(path);
            break;
        case "video":
            openPage(".page.videoviewer");
            videoviewer(path);
            break;
        default:
            window.open(path, "_blank")
            break;
    }
}

function textViewer(path) {
    $(".textviewer-heading").html(path.split("/")[path.split("/").length - 1]);

    $.ajax({
        url: "serverinterface.php?readtextfile&path=" + path,
        dataType: "html",
        success: function(filecontent) {
            $(".textviewer-code").html(hljs.highlightAuto(filecontent)["value"]);
        },
        beforeSend: function() {
            $(".textviewer-code").html("");
            $(".preloader-default")
                .clone()
                .removeClass(".preloader-default")
                .appendTo($(".textviewer-code"));
                }
    });
}

function imageviewer(path) {
    $(".imageviewer-heading").html(path.split("/")[path.split("/").length - 1]);

    $(".imageviewer-imgcontainer")
        .attr("data-caption", path.split("/")[path.split("/").length - 1])
        .attr("onClick", "window.open('" + path + "', '_blank')")
        .attr("src", path);
}

function audioviewer(path) {
    $(".audioviewer-heading").html(path.split("/")[path.split("/").length - 1]);

    $(".audioviewer-audiocontainer")
        .attr("src", path)
        .get(0).play();
}

function videoviewer(path) {
    filename = path.split("/")[path.split("/").length - 1];
    $(".videoviewer-heading").html(filename);

    $(".videoviewer-videocontainer source")
        .attr("src", path)
        .attr("type", "video/" + filename.split(".")[filename.split(".").length - 1]);
    
    $(".videoviewer-videocontainer").get(0).play();
}


function changeView(view) {
    $("#viewmodal").closeModal();
    $(".filetable-mastercontainer")
        .removeClass("layout-list")
        .removeClass("layout-default")
        .removeClass("layout-large")
        .addClass("layout-" + view);
}

function showPathModal() {
    $('.pathmodal-currentpath').html(currentDir);
    $('.pathmodal-url').html(window.location.href);
    $('#pathmodal').openModal();
}


function error(code) {
    switch(code) {
        case "forbiddenpath":
            message = "You can't access this folder."
            break;
        default:
            message = "Can't open this folder.";
            break;
    }

    Materialize.toast(message, 4000)
}


function saveSettings() {
    settings = {
        "base_path": $(".page.settings .base_path").val(),
        "title": $(".page.settings .title").val(),

        "color_scheme": $(".page.settings .select-color:checked").val(),

        "imagepreview": "\"" + $(".page.settings .imagepreview").prop( "checked" ) == "on" + "\"",
        "imagepreview_prerender_thumbnails": "\"" + $(".page.settings .imagepreview_prerender_thumbnails").prop( "checked" ) == "on" + "\"",

        "viewfiles_blacklist": []
    }

    $.each($(".page.settings .blacklist").val().split("\n"), function(i, el) {
        settings["viewfiles_blacklist"].push(el);
    });

    if($(".page.settings .userfield").val() != "" && $(".page.settings .pswfield").val() != "") {
        settings["users"] = [{
            "name": $(".page.settings .userfield").val(),
            "password": $(".page.settings .pswfield").val(),
            "type": "admin"
        }];
    }

    $.ajax({
        url: "serverinterface.php?changesettings",
        data: {
            "settings": JSON.stringify(settings)
        },
        method: "POST",
        dataType: "text",
        success: function(response) {
            console.log(response);
            if(response == "true") {
                window.open("?settingssaved", "_self");
                //Materialize.toast('Settings saved.', 4000);
            } else if(response == "notloggedin") {
                Materialize.toast('You are not logged in.', 4000);
            } else {
                Materialize.toast('An error occured.', 4000);
            }
        }
    });

}


function login() {
    username = $(".page.login .userfield").val();
    password = $(".page.login .pswfield").val();

    $.ajax({
        url: "serverinterface.php?login",
        data: {
            "user": username,
            "psw": password
        },
        method: "POST",
        dataType: "text",
        success: function(response) {
            console.log(response);
            if(response == "true") {
                window.open("?loginSuccess", "_self");
            } else {
                Materialize.toast('Login failed.', 4000);
            }
        }
    });
}


function logout() {
    $.ajax({
        url: "serverinterface.php?logout",
        dataType: "text",
        success: function(response) {
            window.open("?logoutSucess", "_self");
        }
    });
}



function setTitle() {
    var title = $(".headtitle").html();

    title = title.replace(/##[\s\S]*?##/g, function(match) {
        return "<span class='color-text text-lighten-3'>" + match.replace(/#/g, "") + "</span>";
    });

    $(".headtitle").html(title);
}



function setColor(color) {
    colorscheme = color;

    $.each(colors, function(i, col) {
        $("." + col).removeClass(col);
        $("." + col + "-text").removeClass(col + "-text");
    });

    $(".color").addClass(color);
    $(".color" + "-text").addClass(color + "-text");
}


function filesizeRedo(size) {
    name = "b";

    if(size > 1024) {
        size /= 1024;
        name = " Kilobyte";
    }
    if(size > 1024) {
        size /= 1024;
        name = " Megabyte";
    }
    if(size > 1024) {
        size /= 1024;
        name = " Gigabyte";
    }
    if(size > 1024) {
        size /= 1024;
        name = " Terabyte";
    }

    return Math.round(size * 1000) / 1000 + name;
}


filelisticons = {
    default: "description",
    folder: "folder",

    php: "code",
    css: "code",
    less: "code",
    sass: "code",
    xml: "code",
    json: "code",
    js: "code",
    cp: "code",
    h: "code",
    py: "code",
    pyc: "code",

    htm: "cloud",
    html: "cloud",

    pdf: "book",
    txt: "description",

    jpg: "image",
    jpeg: "image",
    gif: "image",
    png: "image",
    bmp: "image",

    avi: "videocam",
    mpg: "videocam",
    mp4: "videocam",
    mkv: "videocam",

    mp3: "audiotrack",
    ogg: "audiotrack"
};

fileviewertypes = {
    "default": ["web"],

    "php": ["web", "text"],
    "htm": ["web", "text"],
    "html": ["web", "text"],
    "css": ["text"],
    "less": ["text"],
    "sass": ["text"],
    "xml": ["text"],
    "json": ["text"],
    "js": ["text"],
    "cp": ["text"],
    "h": ["text"],
    "py": ["text"],
    "txt": ["text"],

    "jpg": ["image", "web"],
    "jpeg": ["image", "web"],
    "gif": ["image", "web"],
    "png": ["image", "web"],
    "bmp": ["image", "web"],

    "avi": ["video"],
    "mpg": ["video"],
    "mp4": ["video"],
    "mkv": ["video"],

    "mp3": ["audio"],
    "ogg": ["audio"]
};

fileviewertypes_text = {
    "web": "Open as new tab",
    "text": "View content",
    "image": "View image",
    "video": "Watch video",
    "audio": "Listen"
};

previewExtensions = [
    "jpg", "jpeg", "gif", "png"
];

colors = [
    "red",
    "pink",
    "purple",
    "deep-purple",
    "indigo",
    "blue",
    "light-blue",
    "cyan",
    "teal",
    "green",
    "light-green",
    "lime",
    "yellow",
    "amber",
    "orange",
    "deep-orange",
    "brown",
    "grey",
    "blue-grey",
    "black"
]