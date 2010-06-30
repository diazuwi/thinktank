{include file="_installer.header.tpl"}
  <div class="container">
    <div id="thinktank-tabs">
      <div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
        <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
          <li id="step-tab-1" class="ui-state-default ui-corner-top">
            <div class="key-stat install_step">
            <h1><span class="pass_step" id="pass-step-1">1</span></h1>
            <h3>Requirements Check</h3>
            </div>  
          </li>
          <li id="step-tab-2" class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active">
            <div class="key-stat install_step">
            <h1>2</h1>
            <h3>Database Setup and Site Configuration</h3>
            </div>
          </li>
          <li id="step-tab-3" class="no-border ui-state-default ui-corner-top">
            <div class="key-stat install_step">
            <h1>3</h1>
            <h3>Finish</h3>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
  
  <div id="installer-page" class="container_24 round-all">
    <img id="dart2" class="dart" alt="" src="/assets/img/dart_wht.png">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
        <form class="input" name="form1" method="post" action="index.php?step=3">
          <h2 class="clearfix step_title">Database Setup</h2>
          <div class="clearfix info_message">
            <strong>My Dear!</strong> ThinkTank need database credentials information, so please fill
            input fields below with valid values. This information will be written into config.inc.php
          </div>
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Database Name</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="db_name" id="db_name"{if isset($db_name)} value="{$db_name}"{/if}>
              <span class="input_information">The name of the MySQL database your ThinkTank data will be stored in.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>User Name</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="db_user" id="db_user"{if isset($db_user)} value="{$db_user}"{/if}>
              <span class="input_information">Your MySQL username.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Password</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="db_passwd" id="db_passwd"{if isset($db_passwd)} value="{$db_passwd}"{/if}>
              <span class="input_information">Your MySQL password.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Database Host</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="db_host" id="db_host"{if isset($db_host)} value="{$db_host}"{/if}>
              <span class="input_information">This is usually <strong>localhost</strong> or a host name provided by the hosting provide.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Database Socket</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="db_socket" id="db_socket"{if isset($db_socket)} value="{$db_socket}"{/if}>
              <span class="input_information">Leave it blanks if you're not sure about this.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Database Port</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="db_port" id="db_port"{if isset($db_port)} value="{$db_port}"{/if}>
              <span class="input_information">Leave it blanks if you're not sure about this.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Table Prefix</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="db_prefix" id="db_prefix"{if isset($db_prefix)} value="{$db_prefix}"{/if}>
              <span class="input_information">Prefix of your table name</span>
            </div>
          </div>
          
          <h2 class="clearfix step_title">Site Configuration</h2>
          <div class="clearfix info_message">
            <strong>My Dear!</strong> ThinkTank need a name and your email (for administrator). 
            This information will be written into config.inc.php
          </div>
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Site Name</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="site_name" id="site_name"{if isset($site_name)} value="{$site_name}"{/if}>
              <span class="input_information">The name of your ThinkTank site.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Your Name</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="owner_name" id="owner_name"{if isset($owner_name)} value="{$owner_name}"{/if}>
              <span class="input_information">Your name</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Your E-mail</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="site_email" id="site_email"{if isset($site_email)} value="{$site_email}"{/if}>
              <span class="input_information">This will be the email address of the ThinkTank site administrator.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Your Country</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <select name="country" id="select8">
                <option value="Afghanistan">Afghanistan</option>
                <option value="Albania">Albania</option>
                <option value="Algeria">Algeria</option>
                <option value="Andorra">Andorra</option>
                <option value="Anguila">Anguila</option>
                <option value="Antarctica">Antarctica</option>
                <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                <option value="Argentina">Argentina</option>
                <option value="Armenia ">Armenia </option>
                <option value="Aruba">Aruba</option>
                <option value="Australia">Australia</option>
                <option value="Austria">Austria</option>
                <option value="Azerbaidjan">Azerbaidjan</option>
                <option value="Bahamas">Bahamas</option>
                <option value="Bahrain">Bahrain</option>
                <option value="Bangladesh">Bangladesh</option>
                <option value="Barbados">Barbados</option>
                <option value="Belarus">Belarus</option>
                <option value="Belgium">Belgium</option>
                <option value="Belize">Belize</option>
                <option value="Bermuda">Bermuda</option>
                <option value="Bhutan">Bhutan</option>
                <option value="Bolivia">Bolivia</option>
                <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                <option value="Brazil">Brazil</option>
                <option value="Brunei">Brunei</option>
                <option value="Bulgaria">Bulgaria</option>
                <option value="Cambodia">Cambodia</option>
                <option value="Canada">Canada</option>
                <option value="Cape Verde">Cape Verde</option>
                <option value="Cayman Islands">Cayman Islands</option>
                <option value="Chile">Chile</option>
                <option value="China">China</option>
                <option value="Christmans Islands">Christmans Islands</option>
                <option value="Cocos Island">Cocos Island</option>
                <option value="Colombia">Colombia</option>
                <option value="Cook Islands">Cook Islands</option>
                <option value="Costa Rica">Costa Rica</option>
                <option value="Croatia">Croatia</option>
                <option value="Cuba">Cuba</option>
                <option value="Cyprus">Cyprus</option>
                <option value="Czech Republic">Czech Republic</option>
                <option value="Denmark">Denmark</option>
                <option value="Dominica">Dominica</option>
                <option value="Dominican Republic">Dominican Republic</option>
                <option value="Ecuador">Ecuador</option>
                <option value="Egypt">Egypt</option>
                <option value="El Salvador">El Salvador</option>
                <option value="Estonia">Estonia</option>
                <option value="Falkland Islands">Falkland Islands</option>
                <option value="Faroe Islands">Faroe Islands</option>
                <option value="Fiji">Fiji</option>
                <option value="Finland">Finland</option>
                <option value="France">France</option>
                <option value="French Guyana">French Guyana</option>
                <option value="French Polynesia">French Polynesia</option>
                <option value="Gabon">Gabon</option>
                <option value="Germany">Germany</option>
                <option value="Gibraltar">Gibraltar</option>
                <option value="Georgia">Georgia</option>
                <option value="Greece">Greece</option>
                <option value="Greenland">Greenland</option>
                <option value="Grenada">Grenada</option>
                <option value="Guadeloupe">Guadeloupe</option>
                <option value="Guatemala">Guatemala</option>
                <option value="Guinea-Bissau">Guinea-Bissau</option>
                <option value="Guinea">Guinea</option>
                <option value="Haiti">Haiti</option>
                <option value="Honduras">Honduras</option>
                <option value="Hong Kong">Hong Kong</option>
                <option value="Hungary">Hungary</option>
                <option value="Iceland">Iceland</option>
                <option value="India">India</option>
                <option value="Indonesia">Indonesia</option>
                <option value="Ireland">Ireland</option>
                <option value="Israel">Israel</option>
                <option value="Italy">Italy</option>
                <option value="Jamaica">Jamaica</option>
                <option value="Japan">Japan</option>
                <option value="Jordan">Jordan</option>
                <option value="Kazakhstan">Kazakhstan</option>
                <option value="Kenya">Kenya</option>
                <option value="Kiribati ">Kiribati </option>
                <option value="Kuwait">Kuwait</option>
                <option value="Kyrgyzstan">Kyrgyzstan</option>
                <option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
                <option value="Latvia">Latvia</option>
                <option value="Lebanon">Lebanon</option>
                <option value="Liechtenstein">Liechtenstein</option>
                <option value="Lithuania">Lithuania</option>
                <option value="Luxembourg">Luxembourg</option>
                <option value="Macedonia">Macedonia</option>
                <option value="Madagascar">Madagascar</option>
                <option value="Malawi">Malawi</option>
                <option value="Malaysia ">Malaysia </option>
                <option value="Maldives">Maldives</option>
                <option value="Mali">Mali</option>
                <option value="Malta">Malta</option>
                <option value="Marocco">Marocco</option>
                <option value="Marshall Islands">Marshall Islands</option>
                <option value="Mauritania">Mauritania</option>
                <option value="Mauritius">Mauritius</option>
                <option value="Mexico">Mexico</option>
                <option value="Micronesia">Micronesia</option>
                <option value="Moldavia">Moldavia</option>
                <option value="Monaco">Monaco</option>
                <option value="Mongolia">Mongolia</option>
                <option value="Myanmar">Myanmar</option>
                <option value="Nauru">Nauru</option>
                <option value="Nepal">Nepal</option>
                <option value="Netherlands Antilles">Netherlands Antilles</option>
                <option value="Netherlands">Netherlands</option>
                <option value="New Zealand">New Zealand</option>
                <option value="Niue">Niue</option>
                <option value="North Korea">North Korea</option>
                <option value="Norway">Norway</option>
                <option value="Oman">Oman</option>
                <option value="Pakistan">Pakistan</option>
                <option value="Palau">Palau</option>
                <option value="Panama">Panama</option>
                <option value="Papua New Guinea">Papua New Guinea</option>
                <option value="Paraguay">Paraguay</option>
                <option value="Peru ">Peru </option>
                <option value="Philippines">Philippines</option>
                <option value="Poland">Poland</option>
                <option value="Portugal ">Portugal </option>
                <option value="Puerto Rico">Puerto Rico</option>
                <option value="Qatar">Qatar</option>
                <option value="Republic of Korea Reunion">Republic of Korea Reunion</option>
                <option value="Romania">Romania</option>
                <option value="Russia">Russia</option>
                <option value="Saint Helena">Saint Helena</option>
                <option value="Saint kitts and nevis">Saint kitts and nevis</option>
                <option value="Saint Lucia">Saint Lucia</option>
                <option value="Samoa">Samoa</option>
                <option value="San Marino">San Marino</option>
                <option value="Saudi Arabia">Saudi Arabia</option>
                <option value="Seychelles">Seychelles</option>
                <option value="Singapore">Singapore</option>
                <option value="Slovakia">Slovakia</option>
                <option value="Slovenia">Slovenia</option>
                <option value="Solomon Islands">Solomon Islands</option>
                <option value="South Africa">South Africa</option>
                <option value="Spain">Spain</option>
                <option value="Sri Lanka">Sri Lanka</option>
                <option value="St.Pierre and Miquelon">St.Pierre and Miquelon</option>
                <option value="St.Vincent and the Grenadines">St.Vincent and the Grenadines</option>
                <option value="Sweden">Sweden</option>
                <option value="Switzerland">Switzerland</option>
                <option value="Syria">Syria</option>
                <option value="Taiwan ">Taiwan </option>
                <option value="Tajikistan">Tajikistan</option>
                <option value="Thailand">Thailand</option>
                <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                <option value="Turkey">Turkey</option>
                <option value="Turkmenistan">Turkmenistan</option>
                <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
                <option value="Ukraine">Ukraine</option>
                <option value="UAE">UAE</option>
                <option value="UK">UK</option>
                <option value="USA" selected="true">USA</option>
                <option value="Uruguay">Uruguay</option>
                <option value="Uzbekistan">Uzbekistan</option>
                <option value="Vanuatu">Vanuatu</option>
                <option value="Vatican City">Vatican City</option>
                <option value="Vietnam">Vietnam</option>
                <option value="Virgin Islands (GB)">Virgin Islands (GB)</option>
                <option value="Virgin Islands (U.S.) ">Virgin Islands (U.S.) </option>
                <option value="Wallis and Futuna Islands">Wallis and Futuna Islands</option>
                <option value="Yemen">Yemen</option>
                <option value="Yugoslavia">Yugoslavia</option>
              </select>
              <span class="input_information">Your Country.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_10 prefix_9 left">
              <input type="submit" name="Submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Next Step &raquo">
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
{include file="_installer.footer.tpl"}