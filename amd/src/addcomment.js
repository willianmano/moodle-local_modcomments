/**
 * Add comment js logic.
 *
 * @package
 * @subpackage local_modcomments
 * @copyright  2022 Willian Mano {@link https://conecti.me}
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 */
/* eslint-disable */
define(['jquery', 'core/ajax', 'local_modcomments/sweetalert'], function($, Ajax, Swal) {
    var AddComment = function(courseid, cmid, modname) {
        this.courseid = courseid;
        this.cmid = cmid;
        this.modname = modname;

        this.registerEventListeners();
    };

    /**
     * @var {int} courseid
     * @private
     */
    AddComment.prototype.courseid = -1;

    /**
     * @var {int} cmid
     * @private
     */
    AddComment.prototype.cmid = -1;

    /**
     * @var {string} modname
     * @private
     */
    AddComment.prototype.modname = '';

    AddComment.prototype.registerEventListeners = function() {
        $(".comment-form").submit(function(event) {
            event.preventDefault();

            this.saveComment($('.comment-form .comment-input').val());
        }.bind(this));
    };

    AddComment.prototype.saveComment = function(comment) {
        if (comment === '') {
            return;
        }

        var request = Ajax.call([{
            methodname: 'local_modcomments_addcomment',
            args: {
                courseid: this.courseid,
                cmid: this.cmid,
                modname: this.modname,
                comment: comment
            }
        }]);

        request[0].done(function(data) {
            this.addCommentContainer(data);
        }.bind(this)).fail(function(error) {
            var message = error.message;

            if (!message) {
                message = error.error;
            }

            this.showToast('error', message);
        }.bind(this));
    };

    AddComment.prototype.showToast = function(type, message) {
        var Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 8000,
            timerProgressBar: true,
            onOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({
            icon: type,
            title: message
        });
    };

    AddComment.prototype.addCommentContainer = function(data) {
        var targetdiv = $('.comments-container');
        var userimg = $('.newcomment .userimg img').clone();
        var userfullname = userimg.attr('alt');

        var newcomment = $("<div class='comment fadeIn'>" +
            "<div class='userinfo'>" +
                "<div class='userimg'>" + $('<div/>').append(userimg).html() + "</div>" +
                    "<div class='nameanddate'>" +
                        "<p class='username'>" + userfullname + "</p>" +
                        "<span class='small'>" + data.humantimecreated + "</span>"+
                    "</div>"+
                "</div>"+
                "<p class='text'>" + data.comment + "</p>" +
            "</div>");

        targetdiv.prepend(newcomment);

        $('.comment-form .comment-input').val('');
    };

    return {
        'init': function(courseid, cmid, modname) {
            return new AddComment(courseid, cmid, modname);
        }
    };
});
