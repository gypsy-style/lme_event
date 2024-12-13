jQuery(document).ready( function($){

    // we create a copy of the WP inline edit post function
    let $wpar_inline_editor = inlineEditPost.edit;

    // Note: Hooking inlineEditPost.edit must be done in a JS script, loaded after wp-admin/js/inline-edit-post.js
    // then we overwrite the inlineEditPost.edit function with our own code
    inlineEditPost.edit = function(id) {

        // call the original WP edit function
        $wpar_inline_editor.apply(this, arguments);


        // ### start: add our custom functionality below  ###

        // get the post ID
        let $post_id = 0;
        if (typeof(id) == 'object') {
            $post_id = parseInt(this.getId(id));
        }


        // if we have our post
        if ($post_id != 0) {
            // tips: use the inspecttion tool to help you see the HTML structure on the edit page.

            // explanation:
            // On the posts management page, all posts will render inside the <tbody> along with "the-list" id.
            // Then each post will render on each <tr> along with "post-176" which 176 is my post ID. Your will be difference.
            // When the quick edit menu is clicked on the "post-176", the <tr> will be set as hide(display:none)
            // and the new <tr> along with "edit-176" id will be appended after <tr> which is hidden.
            // What we will do, we will use the jQuery to find the website value from the hidden <tr>.
            // Get that value and assign to the website input field on the quick edit box.
            //
            // The concept is the same when you create the inline editor by jQuery manually.

            // define the edit row
            let $edit_row = $('#edit-' + $post_id);
            let $post_row = $('#post-' + $post_id);

            // get the data
            let $richmenu_id = $('.line_user_richmenu_id', $post_row).text();
            let $richmenu_outline = $('.line_user_richmenu_outline', $post_row).text();
            console.log($edit_row);

            // populate the data
            $('select.quick_edit_richmenu_id', $edit_row).val($richmenu_id);
            $('.outline', $edit_row).text($richmenu_outline);
        }

        // ### end: add our custom functionality below  ###
    }

});