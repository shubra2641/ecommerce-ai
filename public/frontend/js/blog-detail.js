/**
 * Blog Detail Page JavaScript
 * Handles comment replies and form interactions
 */

$(document).ready(function(){
    (function($) {
        "use strict";

        $('.btn-reply.reply').click(function(e){
            e.preventDefault();
            $('.btn-reply.reply').show();

            $('.comment_btn.comment').hide();
            $('.comment_btn.reply').show();

            $(this).hide();
            $('.btn-reply.cancel').hide();
            $(this).siblings('.btn-reply.cancel').show();

            var parent_id = $(this).data('id');
            var html = $('#commentForm');
            $( html).find('#parent_id').val(parent_id);
            $('#commentFormContainer').hide();
            $(this).parents('.comment-list').append(html).fadeIn('slow').addClass('appended');
        });

        $('.comment-list').on('click','.btn-reply.cancel',function(e){
            e.preventDefault();
            $(this).hide();
            $(this).siblings('.btn-reply.reply').show();
            $('.comment_btn.comment').show();
            $('.comment_btn.reply').hide();

            var html = $('#commentForm');
            $('#commentFormContainer').show();
            $(this).parents('.comment-list').find('.appended').remove();
        });

    })(jQuery);
});
