$(function () {

    // Settings up the tree - using $(selector).jstree(options);
    // All those configuration options are documented in the _docs folder
    $("#structureList").jstree({
        // the list of plugins to include
        "plugins": ["themes", "json_data", "ui", "crrm", "cookies", "dnd", "search", "types", "hotkeys", "contextmenu"],
        // Plugin configuration
        // I usually configure the plugin that handles the data first - in this case JSON as it is most common
        "json_data": {
            // I chose an ajax enabled tree - again - as this is most common, and maybe a bit more complex
            // All the options are the same as jQuery's except for `data` which CAN (not should) be a function
            "ajax": {
                // the URL to fetch the data
                "url": "/admin/ajax/structure.php",
                // this function is executed in the instance's scope (this refers to the tree instance)
                // the parameter is the node being loaded (may be -1, 0, or undefined when loading the root nodes)
                "data": function (n) {
                    // the result is fed to the AJAX request `data` option
                    return {
                        "operation": "get_children",
                        "id": n.attr ? n.attr("id").replace("node_", "") : 1
                    };
                }
            }
        },
        // Configuring the search plugin
        "search": {
            // As this has been a common question - async search
            // Same as above - the `ajax` config option is actually jQuery's object (only `data` can be a function)
            "ajax": {
                "url": "/admin/ajax/structure.php",
                // You get the search string as a parameter
                "data": function (str) {
                    return {
                        "operation": "search",
                        "search_str": str
                    };
                }
            }
        },
        "contextmenu": {
            "items": function (NODE, TREE_OBJ) {
                var obj = {}; // clear out the defaults

                //thisTree = TREE_OBJ;
                //clear default stuff
                obj['create'] = false;
                obj['rename'] = false;
                obj['remove'] = false;
                obj['ccp'] = false;
                if (NODE.attr('rel') != "instance" && NODE.attr('rel') != "site") {


                    obj['create'] = {
                        "separator_before": false,
                        "separator_after": true,
                        "label": "Create New...",
                        "action": false,
                        "submenu": {
                            "newpage": {
                                "separator_before": false,
                                "separator_after": false,
                                "label": "Page",
                                "action": function (obj) {
                                	
                                	if (this._get_node(obj).attr("id").charAt(0) == 'l') {
                                		window.location = '/admin/content.php?navID=' + this._get_node(obj).attr("id").replace('l', '') + '&new=page';
                                	} else {
                                		window.location = '/admin/content.php?bucketID=' + this._get_node(obj).attr("id").replace('b', '') + '&new=page';
                                	}
                                	
                                    //alert("This is where we would call a creation editor under the ID: " + this._get_node(obj).attr("id"));
                                }
                            },
                            "copy": {
                                "separator_before": false,
                                "icon": false,
                                "separator_after": false,
                                "label": "Outbound Link",
                                "action": function (obj) {
                                   if (this._get_node(obj).attr("id").charAt(0) == 'l') {
                                		window.location = '/admin/content.php?navID=' + this._get_node(obj).attr("id").replace('l', '') + '&new=link';
                                	} else {
                                		window.location = '/admin/content.php?bucketID=' + this._get_node(obj).attr("id").replace('b', '') + '&new=link';
                                	}

                                }
                            }
                        }
                    }


                    if (NODE.attr('rel') != "bucket") {

                        if ("outboundinactive" == NODE.attr('rel') || "pageinactive" == NODE.attr('rel')) {
                            obj['activate'] = {
                                "separator_before": false,
                                "separator_after": true,
                                "label": "Activate",
                                "action": function (obj) {
                                    //console.log(this._get_node(obj));
                                    //I would like to place this action down with the other .bind() actions but after an hour of digging I can't figure it out yet
                                    $.post("/admin/ajax/structure.php", {
                                        "operation": "activate_node",
                                        "rel": this._get_node(obj).attr("rel"),
                                        "id": this._get_node(obj).attr("id").replace("node_", "")
                                    }, function (r) {
                                        if (!r.status) {
                                            $.jstree.rollback(this.rlbk);
                                        } else {
                                            //node
                                            nodeResp = "#" + r.id;
                                            //console.log(nodeResp);
                                            //console.log($(nodeResp).attr("rel"));
                                            //console.log(r.relType);
                                            $(nodeResp).attr("rel", r.reltype);
                                            //console.log($(nodeResp).attr("rel"));
                                        }
                                    });

                                }

                            }
                        } else {

                            obj['deactivate'] = {
                                "separator_before": false,
                                "separator_after": true,
                                "label": "Deactivate",
                                "action": function (obj) {
                                    //console.log(this._get_node(obj));
                                    //I would like to place this action down with the other .bind() actions but after an hour of digging I can't figure it out yet
                                    $.post("/admin/ajax/structure.php", {
                                        "operation": "deactivate_node",
                                        "rel": this._get_node(obj).attr("rel"),
                                        "id": this._get_node(obj).attr("id").replace("node_", "")
                                    }, function (r) {
                                        if (!r.status) {
                                            $.jstree.rollback(this.rlbk);
                                        } else {
                                            //node
                                            nodeResp = "#" + r.id;
                                            $(nodeResp).attr("rel", r.reltype);
                                        }
                                    });

                                }
                            }
                        }




                        obj['edit'] = {
                            "separator_before": false,
                            "separator_after": false,
                            "label": "Edit Content/Properties",
                            "action": function (obj) {
                            	window.location = "/admin/content.php?navID=" + this._get_node(obj).attr("id").replace('l', '');
                               // alert("This is where we would call an editor for the ID: " + this._get_node(obj).attr("id")); //this.rename(obj); 
                            }
                        }

                        obj['rename'] = {
                            "separator_before": false,
                            "separator_after": false,
                            "label": "Rename",
                            "action": function (obj) {
                                this.rename(obj);
                            }
                        }

                        obj['remove'] = {
                            "separator_before": false,
                            "icon": false,
                            "separator_after": false,
                            "label": "Delete",
                            "action": function (obj) {
                                if (this.is_selected(obj)) {
                                    if (confirm("Are you sure you want to delete this item and everything underneath it?")) {
                                        this.remove();
                                    }
                                } else {
                                    if (confirm("Are you sure you want to delete this item and everything underneath it?")) {
                                        this.remove(obj);
                                    }
                                }
                            }
                        }
                        obj['ccp2'] = {
                            "separator_before": true,
                            "icon": false,
                            "separator_after": false,
                            "label": "Move...",
                            "action": false,
                            "submenu": {
                                "cut": {
                                    "separator_before": false,
                                    "separator_after": false,
                                    "label": "Cut",
                                    "action": function (obj) {
                                        this.cut(obj);
                                    }
                                },


                                "paste": {
                                    "separator_before": false,
                                    "icon": false,
                                    "separator_after": false,
                                    "label": "Paste",
                                    "action": function (obj) {
                                        this.paste(obj);
                                    }
                                }
                            }
                        }
                    }
                }
                return obj;
            }

        },

        "themes": {
            "theme": "quipp",
            "dots": true,
            "icons": true
        },


        // Using types - most of the time this is an overkill
        // Still meny people use them - here is how
        "types": {
            // I set both options to -2, as I do not need depth and children count checking
            // Those two checks may slow jstree a lot, so use only when needed
            "max_depth": -2,
            "max_children": -2,
            // I want only `drive` nodes to be root nodes 
            // This will prevent moving or creating any other type as a root node
            "valid_children": ["drive"],
            "types": {
                // The default type
                "default": {
                    // I want this type to have no children (so only leaf nodes)
                    // In my case - those are files
                    "valid_children": "none",
                    // If we specify an icon for the default type it WILL OVERRIDE the theme icons
                    "icon": {
                        "image": "/images/admin/jstreeExtras/file.png"
                    }
                },
                // The `folder` type
                "outbound": {
                    // can have files and other folders inside of it, but NOT `drive` nodes
                    "valid_children": ["default", "page", "pageinactive", "pageprotected", "outbound", "outboundinactive", "homepageprotected", "homepage"],
                    "icon": {
                        "image": "/images/admin/jstreeExtras/log_in_16.png"
                    }
                },

                "outboundinactive": {
                    // can have files and other folders inside of it, but NOT `drive` nodes
                    "valid_children": ["default", "page", "pageinactive", "pageprotected", "outbound", "outboundinactive", "homepageprotected", "homepage"],
                    "icon": {
                        "image": "/images/admin/jstreeExtras/log_in_16_inactive.png"
                    }
                },

                "homepage": {
                    // can have files and other folders inside of it, but NOT `drive` nodes
                    "valid_children": ["default", "page", "pageinactive", "pageprotected", "outbound", "outboundinactive", "homepageprotected", "homepage"],
                    "icon": {
                        "image": "/images/admin/jstreeExtras/view_site_16.png"
                    }
                },

                "homepageprotected": {
                    // can have files and other folders inside of it, but NOT `drive` nodes
                    "valid_children": ["default", "page", "pageinactive", "pageprotected", "outbound", "outboundinactive", "homepageprotected", "homepage"],
                    "icon": {
                        "image": "/images/admin/jstreeExtras/view_site_16.png"
                    }
                },

                "page": {
                    // can have files and other folders inside of it, but NOT `drive` nodes
                    "valid_children": ["default", "page", "pageinactive", "pageprotected", "outbound", "outboundinactive", "homepageprotected", "homepage"],
                    "icon": {
                        "image": "/images/admin/jstreeExtras/view_site_blue_16.png"
                    }
                },

                "pageprotected": {
                    // can have files and other folders inside of it, but NOT `drive` nodes
                    "valid_children": ["default", "page"],
                    "icon": {
                        "image": "/images/admin/jstreeExtras/view_site_blue_16.png"
                    }
                },

                "pageinactive": {
                    // can have files and other folders inside of it, but NOT `drive` nodes
                    "valid_children": ["default", "page", "pageinactive", "pageprotected", "outbound", "outboundinactive", "homepageprotected", "homepage"],
                    "icon": {
                        "image": "/images/admin/jstreeExtras/view_site_inactive_16.png"
                    }
                },
                // The `folder` type
                "bucket": {
                    // can have files and other folders inside of it, but NOT `drive` nodes
                    "valid_children": ["default", "page", "pageinactive", "pageprotected", "outbound", "outboundinactive", "homepageprotected", "homepage"],
                    "icon": {
                        "image": "/images/admin/jstreeExtras/template_16.png"
                    },
                    // those options prevent the functions with the same name to be used on the `drive` type nodes
                    // internally the `before` event is used
                    "start_drag": false,
                    "move_node": false,
                    "delete_node": false,
                    "remove": false
                },

                // The `folder` type
                "instance": {
                    // can have files and other folders inside of it, but NOT `drive` nodes
                    "valid_children": ["bucket"],
                    "icon": {
                        "image": "/images/admin/jstreeExtras/network_16.png"
                    },
                    // those options prevent the functions with the same name to be used on the `drive` type nodes
                    // internally the `before` event is used
                    "start_drag": false,
                    "move_node": false,
                    "delete_node": false,
                    "remove": false
                },
                // The `drive` nodes 
                "site": {
                    // can have files and folders inside, but NOT other `drive` nodes
                    "valid_children": ["instance"],
                    "icon": {
                        "image": "/images/admin/jstreeExtras/web_16.png"
                    },
                    // those options prevent the functions with the same name to be used on the `drive` type nodes
                    // internally the `before` event is used
                    "start_drag": false,
                    "move_node": false,
                    "delete_node": false,
                    "remove": false
                }
            }
        },
        // For UI & core - the nodes to initially select and open will be overwritten by the cookie plugin
        // the UI plugin - it handles selecting/deselecting/hovering nodes
        "ui": {
            // this makes the node with ID node_4 selected onload
            "initially_select": ["node_4"]
        },
        // the core plugin - not many options here
        "core": {
            // just open those two nodes up
            // as this is an AJAX enabled tree, both will be downloaded from the server
            "initially_open": ["node_2", "node_3"]
        }
    }).bind("create.jstree", function (e, data) {
        $.post("/admin/ajax/structure.php", {
            "operation": "create_node",
            "id": data.rslt.parent.attr("id").replace("node_", ""),
            "position": data.rslt.position,
            "title": data.rslt.name,
            "type": data.rslt.obj.attr("rel")
        }, function (r) {
            if (r.status) {
                $(data.rslt.obj).attr("id", "node_" + r.id);
            } else {
                $.jstree.rollback(data.rlbk);
            }
        });
    }).bind("remove.jstree", function (e, data) {
        data.rslt.obj.each(function () {
            $.ajax({
                async: false,
                type: 'POST',
                url: "/admin/ajax/structure.php",
                data: {
                    "operation": "remove_node",
                    "id": this.id.replace("node_", "")
                },
                success: function (r) {
                    if (!r.status) {
                        data.inst.refresh();
                    }
                }
            });
        });
    }).bind("rename.jstree", function (e, data) {
        $.post("/admin/ajax/structure.php", {
            "operation": "rename_node",
            "id": data.rslt.obj.attr("id").replace("node_", ""),
            "title": data.rslt.new_name
        }, function (r) {
            if (!r.status) {
                $.jstree.rollback(data.rlbk);
            }
        });
    }).bind("move_node.jstree", function (e, data) {
        data.rslt.o.each(function (i) {
        
        	//serializeAndUpdateNavOrder();
        	//console.log();
        
            $.ajax({
                async: false,
                type: 'POST',
                url: "/admin/ajax/structure.php",
                data: {
                    "operation": "move_node",
                    "id": $(this).attr("id").replace("node_", ""),
                    "siblings": data.rslt.np.find("> ul").serializelist(),
                    "ref": data.rslt.np.attr("id").replace("node_", ""),
                    "position": data.rslt.cp + i,
                    "title": data.rslt.name,
                    "copy": data.rslt.cy ? 1 : 0
                },
                success: function (r) {
                    //console.log(r);
                    if (!r.status) {
                        $.jstree.rollback(data.rlbk);
                    } else {
                        $(data.rslt.oc).attr("id", "node_" + r.id);
                        if (data.rslt.cy && $(data.rslt.oc).children("UL").length) {
                            data.inst.refresh(data.inst._get_parent(data.rslt.oc));
                        }
                    }
                    $("#analyze").click();
                }
            });
        });
    });
});


function serializeAndUpdateNavOrder() {
	
	console.log("Order Of Items: (serializelist 7)");
	//$('#demo1').get_json()
	//console.log($('#structureList ul').get_json());

}
