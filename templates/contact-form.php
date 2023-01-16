<div id="form_sucess"></div>
<div id="form_error"></div>

<form id='enquiry_form' class="lucky-contact-form" autocomplete="off">

    <?php wp_nonce_field('wp_rest'); ?>


    <form action="#">
        <fieldset>
            <input id="name" type="text" name="name" required>
            <label for="name">First Name</label>
            <div class="after"></div>
        </fieldset>
        <fieldset>
            <input id="phone" type="number" name="phone" required>
            <label for="phone">Phone</label>
            <div class="after"></div>
        </fieldset>

        <fieldset>
            <input id="email" type="email" name="email" required>
            <label for="email">Email</label>
            <div class="after"></div>
        </fieldset>
        <fieldset>
            <button type="submit">Submit Form</button>

        </fieldset>


    </form>

    <script>
        jQuery(document).ready(function($) {
            $('#enquiry_form').submit(function(event) {
                event.preventDefault();
                // alert(form.serialize());
                var form = $(this);

                $.ajax({
                    type: "POST",
                    url: "<?php echo get_rest_url(null, 'v1/contact-form/submit'); ?>",
                    data: form.serialize(),
                    success: function() {

                        form.hide();

                        $('#form_success').html('Your message was sent').fadeIn();
                    },
                    error: function() {
                        console.log(form.serialize())
                        $('#form_error').html('Your message can not be sent').fadeIn();
                    }

                });

            })
        })
    </script>