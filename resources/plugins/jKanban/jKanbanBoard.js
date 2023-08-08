//common js code to create Kanban Board
"use strict";
var __spreadArrays = this && this.__spreadArrays || function () {
    for (var s = 0, i = 0, il = arguments.length; i < il; i++) {
        s += arguments[i].length;
    }

    for (var r = Array(s), k = 0, i = 0; i < il; i++) {
        for (var a = arguments[i], j = 0, jl = a.length; j < jl; j++, k++) {
            r[k] = a[j];
        }
    }

return r;
};

var FakeListAPI = /** @class */function () {
    function FakeListAPI() {
        this.lists = [];
        this.loadLists();
    }

    // emulate async API call - Gets all lists.
    FakeListAPI.prototype.getLists = function () {
        return Promise.resolve(_.cloneDeep(this.lists));
    };

    // emulate async API call - Gets single full card.
    FakeListAPI.prototype.getCard = function (id) {
        for (var l = 0; l < this.lists.length; l++) {
            var list = this.lists[l];
            for (var i = 0; i < list.cards.length; i++) {
                var card = list.cards[i];
                if (card.id === id) {
                    return Promise.resolve(card);
                }
            }
        }

        return Promise.reject('ERROR 404');
    };

    FakeListAPI.prototype.loadLists = function () {
        this.lists= [];
    };

    return FakeListAPI;
}();

var KanbanBoard = /** @class */function () {
    function KanbanBoard() {
        this.listApi = new FakeListAPI(); // usually injected or set in constructor
    }

    KanbanBoard.prototype.listToKanbanBoard = function (list) {
        var _this = this;
        var asIsProps = ['id', 'title'];
        var board = _.pick(list, asIsProps);
        board.item = list.cards.map(function (c) {
                return _this.cardToKanbanCard(c);
        });
        board.meta = _.omitBy(list, asIsProps.concat(['cards']));
        return board;
    };

    KanbanBoard.prototype.cardToKanbanCard = function (list) {
        var asIsProps = ['id', 'title'];
        var card = _.pick(list, asIsProps);
        card.meta = _.omitBy(list, asIsProps);
        return card;
    };

    KanbanBoard.prototype.processBoard = function (board) {
        // custom boards
        var _this = this;
        if (board.id === 'not_started') {
            board['class'] = 'new';
            board.dragTo = ['not_started', 'in_progress',
                    'on_hold', 'cancelled', 'completed'
                ];
            board.meta = {
                icon: 'fas fa-exclamation',
                // boardActions: [
                //     { icon: 'fas fa-edit', label: 'Edit' },
                //     { icon: 'fas fa-trash', label: 'Delete' },
                //     { icon: 'fas fa-eye', label: 'View' },
                // ]
            };
        } else if (board.id === 'in_progress') {
            board['class'] = 'info,good';
            board.dragTo = ['not_started', 'in_progress',
                    'on_hold', 'cancelled', 'completed'
                ];
            board.meta = {
                icon: 'fas fa-sync'
            };

        } else if (board.id === 'on_hold') {
            board['class'] = 'warning';
            board.dragTo = ['not_started', 'in_progress',
                    'on_hold', 'cancelled', 'completed'
                ];
            board.meta = {
                icon: 'fas fa-exclamation-triangle'
            };

        }else if (board.id === 'cancelled') {
            board['class'] = 'new';
            board.dragTo = ['not_started', 'in_progress',
                    'on_hold', 'cancelled', 'completed'
                ];
            board.meta = {
                icon: 'fas fa-times-circle'
            };

        } else if (board.id === 'completed') {
            board['class'] = 'success';
            board.dragTo = ['not_started', 'in_progress',
                    'on_hold', 'cancelled', 'completed'
                ];
            board.meta = {
                icon: 'fas fa-check-double'
            };

        }

        // construct board dom html
        var $title = $("<div class=\"board-title\"></div>").text(board.title);
        if (board.meta && board.meta.icon) {
          $title.prepend("<i class=\"" + _.escape(board.meta.icon) + " fa-fw\"></i> ");
        }

        board.title = $title[0].outerHTML;
        if (board.meta.boardActions && board.meta.boardActions.length) {
            var $boardActions_1 = $('<div class="board-actions"></div>');
            board.meta.boardActions.map(function (ba) {
                var $button = $('<button class="action"></button>');
                if (ba.icon) {
                  var $icon = $("<i class=\"" + _.escape(ba.icon) + " fa-fw\"></i>");
                  $button.append($icon);
                }
                if (ba.label) {
                  $button.attr('title', ba.label);
                }
                return $button;
            }).forEach(function ($button) {
                $boardActions_1.append($button);
            });
          board.title += $boardActions_1[0].outerHTML;
        }

        if (board.item && board.item.length) {
            board.item.forEach(function (card) {
                card.parentId = board.id;
                if (card.meta.project_id) {
                    card.project_id = card.meta.project_id;
                }
                _this.processCard(card);
            });
        }
        return board;
    };

    KanbanBoard.prototype.processCard = function (card) {
        var $title = $("<div class=\"card-title\"></div>");
        var genIcon = function (iconClass, title) {
          title = title ? " title=\"" + _.escape(title) + "\"" : '';
          return "<i class=\"" + _.escape(iconClass) + " fa-fw\"" + (title ? " title=\"" + _.escape(title) + "\"" : '') + "></i>";
        };

        var genAction = function (iconClass, title, href, label, additionalClass) {
            var $button = $(href ? '<a class="card-action"></a>' : '<button class="action"></button>');
            if (href && additionalClass) {
                $button.attr('data-href', href);
            } else if (href) {
                $button.attr('href', href);
                $button.addClass('action');
            }

            if (additionalClass) {
                $button.addClass(additionalClass);
            }

            var $icon = $("<i class=\"" + _.escape(iconClass) + " fa-fw\"></i>");
            $button.append($icon);
            if (label) {
                $button.append(label);
            }
            if (title) {
                $button.attr('title', title);
            }
          return $button;
        };

        var $cardActions = $('<div class="card-actions"></div>');

        if (card.meta) {
            if (card.meta.provider) {
                switch (card.meta.provider) {
                    case 'bitbucket':
                        $title.append(genIcon('fab fa-bitbucket text-info', 'BitBucket Issue'));
                        break;
                    case 'slack':
                        $title.append(genIcon('fab fa-slack text-warning', 'Slack Chat'));
                        break;
                    case 'zendesk':
                        $title.append(genIcon('fas fa-life-ring text-success', 'Zendesk Ticket'));
                        break;
                    case 'google-drive':
                        $title.append(genIcon('fab fa-google-drive text-info', 'Google Drive'));
                        break;
                    case 'trello':
                        $title.append(genIcon('fab fa-trello text-info', 'Trello'));
                        break;
                    case 'bitbucket':
                        $title.append(genIcon('fab fa-bitbucket text-info', 'BitBucket Issue'));
                        break;
                    case 'calendar':
                        $title.append(genIcon('far fa-calendar', 'Calendar'));
                        break;
                    case 'google-keep':
                        $title.append(genIcon('fas fa-sticky-note text-warning', 'Google Keep'));
                        break;
                    case 'bitbucket':
                        $title.append(genIcon('fab fa-bitbucket text-info', 'BitBucket Issue'));
                        break;
                    case 'email':
                        $title.append(genIcon('fas fa-at text-danger', 'Email'));
                        break;
                }
            }

            if (card.meta.type) {
                switch (card.meta.type) {
                    case 'bug':
                        $title.append(genIcon('fas fa-bug text-danger', 'Bug'));
                        break;
                    case 'comment':
                        $title.append(genIcon('fas fa-comment text-muted', 'Comment'));
                        break;
                    case 'comment-private':
                        $title.append(genIcon('fas fa-comment text-danger', 'Private Comment'));
                        break;
                    case 'comment-public':
                        $title.append(genIcon('fas fa-comment text-info', 'Public Comment'));
                        break;
                    case 'ticket':
                        $title.append(genIcon('fas fa-comment text-muted', 'Ticket'));
                        break;
                    case 'notification':
                        $title.append(genIcon('fas fa-exclamation-circle text-muted', 'Notification'));
                        break;
                    case 'card':
                        $title.append(genIcon('far fa-clone text-muted', 'Card'));
                        break;
                    case 'board':
                        $title.append(genIcon('far fa-columns text-muted', 'Board'));
                        break;
                    case 'folder':
                        $title.append(genIcon('fas fa-folder text-muted', 'Folder'));
                        break;
                    case 'calendar-event':
                        $title.append(genIcon('fas fa-calendar-alt text-muted', 'Event'));
                        break;
                    case 'note':
                        $title.append(genIcon('far fa-sticky-note text-muted', 'Note'));
                        break;
                    case 'message':
                        $title.append(genIcon('far fa-envelope text-muted', 'Note'));
                        break;
                }
            }

            if (card.meta.prefix) {
                $title.append("<strong>[" + _.escape(card.meta.prefix) + "]</strong>");
            }

            if (card.meta.editUrl) {
                $cardActions.append(genAction('fas fa-edit', LANG.edit, card.meta.editUrl, '', card.meta.editUrlClass));
            }

            if (card.meta.deleteUrl) {
                $cardActions.append(genAction('fas fa-trash', LANG.delete, card.meta.deleteUrl, '', card.meta.deleteUrlClass));
            }

            if (card.meta.viewUrl) {
                $cardActions.append(genAction('fas fa-eye', LANG.view, card.meta.viewUrl, '', card.meta.viewUrlClass));
            }

        //append default actions - probably should also be context sensitive
        // $cardActions.append(genAction('fas fa-check text-success', 'Accept', null, "<span class=\"text-success\">" + 'Accept' + "</span>"));

        // $cardActions.append(genAction('fas fa-times text-danger', 'Decline'));

        // $cardActions.append(genAction('fas fa-ellipsis-v', 'More...'));
        }

        $title.append(' ' + _.escape(card.title));

        if (card.meta.subtitle) {
            $title.append(' ' + '<code>'+ card.meta.subtitle +'</code>');
        }

        if ($cardActions.length) {
            $title.append($cardActions);
        }

        card.title = $title[0].outerHTML;

        // tags
        var $cardTags = $('<div class="card-tags"></div>');
        if (card.meta) {

            if (card.meta.project) {
                $cardTags.append("<span class=\"label label-default\" title=\"" + LANG.project + "\"><i class=\"fas fa-check-circle\"></i> " + card.meta.project + "</span>");
            }

            if (card.meta.dueDate) {
                var dateStr = moment(card.meta.dueDate).format(moment_date_format);
                $cardTags.append("<span class=\"label label-danger\" title=\"" + LANG.this_card_has_a_due_date + "\"><i class=\"fas fa-clock\"></i> " + dateStr + "</span>");
            }

            if (card.meta.endDate) {
                var dateStr = moment(card.meta.endDate).format(moment_date_format);
                $cardTags.append("<span class=\"label label-danger\" title=\"" + LANG.this_card_has_a_end_date + "\"><i class=\"fas fa-clock\"></i> " + dateStr + "</span>");
            }

            if (card.meta.hasDescription) {
                $cardTags.append("<span class=\"label label-default\" title=\"" + LANG.this_card_has_a_description + "\"><i class=\"fas fa-align-left\"></i></span>");
            }

            if (card.meta.hasComments) {
                $cardTags.append("<span class=\"label label-default\" title=\"" + LANG.this_card_has_comments + "\"><i class=\"fas fa-comment\"></i></span>");
            }

            if (card.meta.lead) {
                $cardTags.append("<span class=\"label label-default\" title=\"" + LANG.lead + "\"><i class=\"fas fa-user-tie\"></i> " + card.meta.lead + "</span>");
            }
            
            if (card.meta.customer) {
                $cardTags.append("<span class=\"label label-default\" title=\"" + LANG.customer + "\"><i class=\"fa fa-briefcase\"></i> " + card.meta.customer + "</span>");
            }

            if (!_.isEmpty(card.meta.assigned_to)) {
                $cardTags.append('</br>');
                _.forEach(card.meta.assigned_to, function(value, key) {
                    $cardTags.append('<img class="user_avatar" src="' + value +'" data-toggle="tooltip" title="'+key+'">');
                });
                $cardTags.append('</br>');
            }

            // if (card.meta.isWatching) {
            //     $cardTags.append("<span class=\"badge badge-light text-muted\" title=\"" + _.escape('You are watching this card for changes.') + "\"><i class=\"fas fa-eye\"></i></span>");
            // }

            if (card.meta.tags && card.meta.tags.length) {
                var tagsToDisplay = 5;
                for (var i = 0; i < card.meta.tags.length && i < tagsToDisplay; i++) {
                    var $tag = $("<span class=\"label label-default\"></span>");
                    $tag.text(card.meta.tags[i]);
                    $cardTags.append($tag);
                }

                if (card.meta.tags.length > tagsToDisplay) {
                    var extraTags = card.meta.tags.length - tagsToDisplay;
                    var $extraTags = $("<span class=\"text-muted\">+" + extraTags + LANG.more + "</span>");
                    $cardTags.append($extraTags);
                }
            }
        }

        if ($cardTags[0].hasChildNodes()) {
            card.title += $cardTags[0].outerHTML;
        }

        // var $cardFooterActions = $('<div class="footer-actions"></div>');

        // if (card.meta.overviewTabUrl) {
        //     $cardFooterActions.append(genAction('fas fa-tachometer-alt', LANG.overview, card.meta.overviewTabUrl, '', ''));   
        // }

        // if (card.meta.activitiesTabUrl) {
        //     $cardFooterActions.append(genAction('fas fa-chart-line', LANG.activities, card.meta.activitiesTabUrl, '', ''));   
        // }

        // if (card.meta.taskTabUrl) {
        //     $cardFooterActions.append(genAction('fa fa-tasks', LANG.task, card.meta.taskTabUrl, '', ''));   
        // }

        // if (card.meta.timeLogTabUrl) {
        //     $cardFooterActions.append(genAction('fas fa-clock', LANG.time_logs, card.meta.timeLogTabUrl, '', ''));   
        // }

        // if (card.meta.docNoteTabUrl) {
        //     $cardFooterActions.append(genAction('fas fa-file-image', LANG.documents_and_notes, card.meta.docNoteTabUrl, '', ''));   
        // }

        // if (card.meta.invoiceTabUrl) {
        //     $cardFooterActions.append(genAction('fa fa-file', LANG.invoices, card.meta.invoiceTabUrl, '', ''));   
        // }

        // if (card.meta.settingsTabUrl) {
        //     $cardFooterActions.append(genAction('fa fa-cogs', LANG.settings, card.meta.settingsTabUrl, '', ''));   
        // }

        // var $cardFooter = $('<div class="card-footer"></div>');

        // if ($cardFooterActions.length) {
        //     $cardFooter.append($cardFooterActions);
        // }

        // card.title += $cardFooter[0].outerHTML;
        
        return card;
    };

    KanbanBoard.prototype.findCard = function (id) {
        for (var l = 0; l < this.lists.length; l++) {
            var list = this.lists[l];
            for (var i = 0; i < list.cards.length; i++) {
                var card = list.cards[i];
                if (card.id == id) {
                    return card;
                }
            }
        }
        return undefined;
    };

    KanbanBoard.prototype.setupUI = function (kanbanTest) {
        // todo
    };

    /* TODO: creates and opens a modal dialog for the full card object */
    // KanbanBoard.prototype.openCardModal = function (card) {
    //     console.warn('Stub(openCardModal): ' + 'Dialog is unimplemented!'); // debug
    //     // prepare modal dom
    //     $('#cardModalTitle').text(card.title);
    //     $('#debug-modal-model').text(JSON.stringify(card, null, 2));
    //     // open modal
    //     var options = {};
    //     $('#cardModal').modal(options);
    // };

  return KanbanBoard;
}();

var isDraggingCard = false;

function initializeAutoScrollOnKanbanWhileCardDragging(kanban) {
    // avoid propagation of card action button clicks
    $(kanban.element).find('.card-actions .action').on('click', function (e) {
      e.stopPropagation();
    });

    // auto-scroll list on card drag
    $('body').on('mousemove', function (e) {
        if (isDraggingCard) {
            if (e.target.parentElement && e.target.parentElement.dataset.eid) {
                var cardId = e.target.parentElement.dataset.eid;
                var $card = $(kanban.element).find(".kanban-item[data-eid=\"" + cardId + "\"]");
                var kanbanDrag = $card.closest('.kanban-drag')[0];
                var dragRect = kanbanDrag.getBoundingClientRect();
                var top = dragRect.y;
                var y = e.clientY;

                if (y < top + 20) {
                    kanbanDrag.scrollBy(0, -20);
                } else if (y < top + 60) {
                    kanbanDrag.scrollBy(0, -10);
                } else if (y > top + dragRect.height - 20) {
                    kanbanDrag.scrollBy(0, 20);
                } else if (y > top + dragRect.height - 60) {
                    kanbanDrag.scrollBy(0, 10);
                }
            }
        }
    });

    // auto-resize scrollbars on list resize
    var ro = new ResizeObserver(function (entries) {
        // TODO: throttle this
        for (var _i = 0, entries_1 = entries; _i < entries_1.length; _i++) {
            var entry = entries_1[_i];
            entry.target._ps.update();
        }
    });

    $('.kanban-drag').each(function (i, el) {
        el._ps = new PerfectScrollbar(el, { useBothWheelAxes: true });
        ro.observe(el);
    });
}