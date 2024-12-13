<?php

/**
 * Created by PhpStorm.
 * User: sawadakeisuke
 * Date: 9/5/22
 * Time: 5:10 PM
 */

class Html
{

  /**
   * lineIDの取得エラー処理
   * @param mixed $error_message 
   * @param mixed $error_action 
   * @return void 
   */
  static function api_error_handle($error_message, $error_action, $error_message_alert = false)
  {
    $default_error_message_alert = 'lineIDが正常に取得できませんでした。スタッフにお問い合わせください';
    if ($error_message_alert == false) {
      $error_message_alert = $default_error_message_alert;
    }
?>

    let errorMessageAlert = '<?= $error_message_alert; ?>';
    let errorMessage = '<?= $error_message; ?>';
    let errorAction = '<?= $error_action; ?>';
    let errorPost = {error_message:errorMessage,error_action:errorAction};
    $.ajax({
    type: "GET",
    url: "<?= home_url(); ?>/wp-json/wp/v2/line_api_error",
    dataType: "text",
    data:errorPost
    }).done(function(response){
    console.log(response);
    }).fail(function(XMLHttpRequest, textStatus, errorThrown){
    alert(errorThrown);
    });
    alert(errorMessageAlert);


  <?php
  }

  static function store_banner()
  {
    $args = array(
      'post_type' => array('store_banner'), //投稿タイプを指定
      'posts_per_page' => '1', //取得する投稿件数を指定
      'orderby' => 'rand', //投稿の日付を基準にソート
      'order' => 'desc', //最新の投稿を取得するために降順にソート
      'post_status' => 'publish', 
    );
    $the_query = new WP_Query($args);
    if ($the_query->have_posts()) {
      while ($the_query->have_posts()) {
        $the_query->the_post();
        $store_banner_id = get_the_ID();
        $store_name = get_the_title($store_banner_id);
        $store_detail = get_post_meta($store_banner_id, 'store_detail', true);

        $store_url = get_post_meta($store_banner_id, 'store_url', true);
        $store_image = get_the_post_thumbnail($store_banner_id, 'full');
      }
    }
  ?>

    <article class="lmf-ad_area">
      <div class="lmf-ad_block">
        <a href="<?= $store_url; ?>">
          <div class="text_box">
            <h2 class="title"><?= $store_detail; ?></h2>
            <p class="from">AD <?= $store_name; ?></p>
          </div>
          <figure class="fig_box"><?= $store_image; ?></figure>
        </a>
      </div>
    </article>

  <?php
  }

  static function form_javascript($custom_fields)
  {
  ?>
    <script>
      $(function() {
        <?php
        $liff_id_form = get_option('liff_id_form');
        $after_registration_action = get_option('after_registration_action');
        $liff_id_profile = get_option('liff_id_profile');
        ?>
        // 追加
        initializeLiff("<?= $liff_id_form; ?>");
        $('#form').submit(function(event) {
          event.preventDefault();
          let type = 'register';

          let form = document.getElementById('form');
          // console.log(document.forms.line-members-form);
          let formData = new FormData(document.forms.form);
          // console.log(formData);
          let values = formData.values();
          // console.log(values);
          let post = {};
          let pushMessage = [];
          let same_radio;
          let birthdayMessage;
          $("#form :input").each(function() {

            let input = $(this); // This is the jquery object of the input, do what you will
            let input_name = input.attr('name');
            console.log(input_name);
            let type = input.attr('type');
            let val;
            if (input_name) {
              if (type == 'radio') {
                val = $('input[name="' + input_name + '"]:checked').val();
              } else if (type == 'checkbox') {
                let checkbox_val = [];
                $('input[name="' + input_name + '"]:checked').each(function() {
                  checkbox_val.push($(this).val());
                  val = checkbox_val;
                })
              } else {
                val = $('#' + input_name).val();
              }
              console.log(val);
              post[input_name] = val;
              // pushメッセージ作成
              if (input_name == 'birthday_y') {
                birthdayMessage = val + '年';
              } else if (input_name == 'birthday_m') {
                birthdayMessage = birthdayMessage + val + '月';
              } else if (input_name == 'birthday_d') {
                birthdayMessage = birthdayMessage + val + '日';
                pushMessage.push('お誕生日：' + birthdayMessage);
              } else {
                if (!(type == 'radio' && same_radio == input_name) && input_name != 'form_type') {
                  same_radio = input_name;
                  let title = input.attr('data-title');
                  if (title) {
                    pushMessage.push(title + '：' + val);
                  } else {
                    pushMessage.push(val);
                  }
                }
              }
            }
          });
          // pushMessage.push('お誕生日：'+post['birthday_y']+'年'+post['birthday_m']+'月');
          post['line_id'] = userId;
          post['displayName'] = displayName;
          // return false;
          pushMessage = pushMessage.join('\n');
          liff.sendMessages([{
            type: 'text',
            text: pushMessage
          }]);
          // console.log(post);
          // return false;
          $.ajax({
            type: "GET",
            url: "<?= home_url(); ?>/wp-json/wp/v2/register_line_user",
            dataType: "text",
            data: post
          }).done(function(response) {
            <?php
            if ($after_registration_action) {
              if ($after_registration_action == 1) {
                $registration_redirect_url = get_option('registration_redirect_url');
                if (!$registration_redirect_url) :
            ?>
                  liff.closeWindow();
                  return false;
                <?php
                endif;
                ?>
                let redirect_url = "<?= $registration_redirect_url; ?>";
              <?php
              } elseif ($after_registration_action == 2) {
              ?>
                let redirect_url = 'https://liff.line.me/<?= $liff_id_profile; ?>';
              <?php
              }
              ?>
              liff.closeWindow();
              window.close();
              // liff.openWindow({
              //   url: redirect_url
              // });
              window.location = redirect_url;

            <?php
            }
            ?>
            // alert('OK');
            // sendMessage();
            liff.closeWindow();
          }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
            alert(errorThrown);
          });
          event.preventDefault();

        });
      });

      // 追加
      function initializeLiff(liffId) {
        liff
          .init({
            liffId: liffId
          })
          .then(() => {
            if (!liff.isLoggedIn()) {
              liff.login()
            }

            getProfile();
          })
          .catch((err) => {
            console.log('LIFF Initialization failed ', err)
          });
      }
      let userId;
      let displayName;
      let post;

      getProfile = function() {
        liff.getProfile()
          .then(profile => {
            userId = profile.userId;
            displayName = profile.displayName;

          })
          .catch((err) => {
            // alert("liff getProfile error : " + err);
          });
      };
    </script>
  <?php
  }

  static function form_text($title, $item_name)
  {
  ?>
    <h2>
      <?= $title; ?>
    </h2>
    <ul class="form_box">
      <li>
        <input id="<?= $item_name; ?>" name="<?= $item_name; ?>" type="text" data-title="<?= $title; ?>" value="">
      </li>
    </ul>
  <?php
  }

  static function form_datepicker($title, $item_name)
  {
  ?>
    <h2><?= $title; ?></h2>
    <ul class="form_box">
      <li><input id="<?= $item_name; ?>" class="datepicker" name="<?= $item_name; ?>" type="text" data-title="<?= $title; ?>" value=""></li>
    </ul>
  <?php
  }

  static function form_textarea($title, $item_name)
  {
  ?>
    <h2>
      <?= $title; ?>
    </h2>
    <ul class="form_box">
      <li>
        <textarea name="<?= $item_name; ?>" data-title="<?= $title; ?>" rows="4"></textarea>
      </li>
    </ul>
  <?php
  }

  static function form_radio($title, $item_name, $item_options, $required = false)
  {
  ?>
    <dt><label for="<?= $item_name; ?>"><?= $title; ?></label></dt>
    <ul class="form_box form_box_inline">
      <?php
      foreach ($item_options as $key => $item_option):
      ?>
        <li><label><input type="radio" data-title="<?= $title; ?>" id="<?= $item_name; ?>" name="<?= $item_name; ?>" value="<?= $item_option; ?>" <?= $key === 0 && $required ? ' required' : ''; ?>><?= $item_option; ?></label></li>
      <?php
      endforeach;
      ?>
    </ul>
  <?php

  }

  static function form_select($title, $item_name, $item_options, $required = false)
  {
  ?>
    <dt><label for="<?= $item_name; ?>"><?= $title; ?></label></dt>
    <dd><select name="<?= $item_name; ?>" id="<?= $item_name; ?>">
        <option value="">----選択してください----</option>
        <?php
        foreach ($item_options as $key => $item_option):
        ?>
          <option value="<?= $item_option; ?>"><?= $item_option; ?></option>
        <?php
        endforeach;
        ?>
      </select></dd>
  <?php

  }

  static function form_checkbox($title, $item_name, $item_options, $required = false)
  {
  ?>
    <h2><?= $title; ?></h2>
    <ul class="form_box form_box_inline">
      <?php
      foreach ($item_options as $key => $item_option):
      ?>
        <li><label for="<?= $item_name . '-' . ($key + 1); ?>"><input type="checkbox" class="<?= $item_name; ?>" data-title="<?= $title; ?>" id="<?= $item_name . '-' . ($key + 1); ?>" name="<?= $item_name; ?>[]" value="<?= $item_option; ?>" <?= $key === 0 && $required ? ' required' : ''; ?>><?= $item_option; ?></label></li>
      <?php
      endforeach;
      ?>
    </ul>
  <?php

  }

  static function form_birthday()
  {
  ?>
    <h2>お誕生日</h2>
    <ul class="form_box form_box_inline">
      <li>
        <label for="">
          <select name="birthday_y" id="birthday_y" required>
            <?php
            $date_y = date('Y');
            $default_age = 25;
            $max_age = 90;
            $min_age = 5;
            $default_year = ($date_y - $default_age);
            $max_year = ($date_y - $max_age);
            $min_year = ($date_y - $min_age);
            for ($year = $max_year; $year <= $min_year; $year++) :
              $selected = '';
              if ($year == $default_year) {
                $selected = ' selected';
              }
            ?>
              <option value="<?= $year; ?>" <?= $selected; ?>>
                <?= $year; ?>
              </option>
            <?php
            endfor;
            ?>
          </select>
        </label>
      </li>
      <li class="text">年</li>
      <li>
        <label for="">
          <select name="birthday_m" id="birthday_m">
            <?php
            for ($month = 1; $month <= 12; $month++) :
            ?>
              <option value="<?= $month; ?>">
                <?= $month; ?>
              </option>
            <?php
            endfor;
            ?>
          </select>
        </label>
      </li>
      <li class="text">月</li>
      <li>
        <label for="">
          <select name="birthday_d" id="birthday_d">
            <?php
            for ($day = 1; $day <= 31; $day++) :
            ?>
              <option value="<?= $day; ?>">
                <?= $day; ?>
              </option>
            <?php
            endfor;
            ?>
          </select>
        </label>
      </li>
      <li class="text">日</li>
    </ul>
  <?php

  }

  static function profile_text($title, $name)
  {
  ?>
    <?php
    if ($title) :
    ?>
      <div class="<?= $name; ?>_form_inner">
        <h2 id="h2_<?= $name; ?>" class="h2_<?= $name; ?>">
          <?= $title; ?>
        </h2>
      <?php
    endif;
      ?>
      <ul id="ul_<?= $name; ?>" class="form_box ul_<?= $name; ?>">
        <li><span id="<?= $name; ?>"></span></li>
      </ul>
      </div>
    <?php

  }

  static function profile_text_edit($title, $name)
  {
    $min_point = 1;
    $max_point = 13;
    ?>
      <div class="<?= $name; ?>_form_inner">
        <h2>
          <?= $title; ?>
        </h2>
        <ul class="form_box">
          <li> <span>
              <select name="<?= $name; ?>" id="<?= $name; ?>">
                <?php
                for ($i = $min_point; $i <= $max_point; $i++) :
                ?>
                  <option value="<?= $i; ?>"><?= $i; ?></option>
                <?php
                endfor; ?>
              </select>
            </span> </li>
        </ul>
      </div>
  <?php

  }
}
