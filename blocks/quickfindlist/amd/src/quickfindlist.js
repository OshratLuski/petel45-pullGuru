define(
    ['jquery', 'core/str'],
    function($, Str) {
        var priv = {
            sesskey: null,
            instances: []
        };

        var t = {
            init: function(roleid, userfields, url, courseformat, courseid, sesskey) {
                priv.sesskey = sesskey;

                var instance = {
                    'roleid': roleid,
                    'userfields': userfields,
                    'url': url,
                    'courseformat': courseformat,
                    'courseid': courseid,
                    'progress': $('#quickfindprogress'+roleid),
                    'listcontainer': $('#quickfindlist'+roleid),
                    'xhr': null
                };
                priv.instances[roleid] = instance;
                $('#quickfindlistsearch'+roleid).on('keyup', t.search_on_type);
                $('#quickfindform'+roleid).on('submit', t.search_on_submit);
            },

            search_on_type: function(e) {
                var target = $(e.target);
                var searchstring = target.val();
                var roleid = /[\-0-9]+/.exec(target.attr('id'))[0];
                t.search(searchstring, roleid);
                M.util.js_pending('quickfindlist' + roleid);
            },

            search_on_submit: function(e) {
                e.preventDefault();
                var target = $(e.target);
                var roleid = /[\-0-9]+/.exec(target.attr('id'))[0];
                var searchstring = target.find('#quickfindlistsearch'+roleid).val();
                t.search(searchstring, roleid);
                M.util.js_pending('quickfindlist' + roleid);
            },


            search: function(searchstring, roleid) {

                var instance = priv.instances[roleid];

                var url = M.cfg.wwwroot+'/blocks/quickfindlist/quickfind.php';
                var urlloginas = M.cfg.wwwroot+'/course/loginas.php?id=1&sesskey='+M.cfg.sesskey;

                Str.get_strings([
                    {key: 'loginas'},
                ]).done(function (strings) {

                    if (instance.xhr !== null) {
                        instance.xhr.abort();
                    }
                    instance.progress.css('visibility', 'visible');
                    instance.xhr = $.ajax({
                        url: url,
                        data: {
                            role: roleid,
                            name: searchstring,
                            courseformat: instance.courseformat,
                            courseid: instance.courseid,
                            sesskey: priv.sesskey
                        }
                    }).done(function(response) {
                        var list = $('<ul />');
                        for (var p in response.people) {
                            var userstring = instance.userfields.replace('[[firstname]]', response.people[p].firstname);
                            userstring = userstring.replace('[[lastname]]', response.people[p].lastname);
                            userstring = userstring.replace('[[username]]', response.people[p].username);

                            // PTL-9892.
                            var li = '';
                            if (response.people[p].loginas) {
                                li = $('<li class="d-flex"><a class="mr-3" href="' + instance.url+'&id='+response.people[p].id +'">'
                                    + userstring + '</a>' + '<a href="' + urlloginas + '&user=' + response.people[p].id + '">'
                                    + '<i class="fa-light fa-right-to-bracket" title="' + strings[0] + '"></i></a></li>');
                            } else {
                                li = $('<li><a href="' + instance.url + '&id=' + response.people[p].id + '">'
                                    + userstring + '</a></li>');
                            }

                            list.append(li);
                        }
                        $('#quickfindlist' + roleid).replaceWith(list);
                        list.attr('id', 'quickfindlist' + roleid);
                    }).fail(function(jqXHR, status) {
                        if (status !== 'abort') {
                            if (status !== undefined) {
                                instance.listcontainer.html(status);
                            }
                        }
                    }).always(function() {
                        instance.progress.css('visibility', 'hidden');
                        M.util.js_complete('quickfindlist' + roleid);
                    });
                });
            }
        };

        return t;
    }
);
