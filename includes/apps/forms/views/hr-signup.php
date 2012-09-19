
<?php
$provs  = array();
$states = array();
?>
<section id="hrSignup">
    
    <div id="card" class="box">
        <div class="heading">
            <h2>
                HR Signup<br />
                <span>Frequently Asked Questions</span>
            </h2>
        </div>
        <ul>
            <li>
                <h4>This is a frequently asked question</h4>
                <p>Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Nullam quis risus eget urna mollis ornare vel eu leo. Donec id elit non mi porta gravida at</p>
            </li>
            <li>
                <h4>This is a frequently asked question</h4>
                <p>Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Nullam quis risus eget urna mollis ornare vel eu leo. Donec id elit non mi porta gravida at</p>
            </li>
            <li>
                <h4>This is a frequently asked question</h4>
                <p>Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Nullam quis risus eget urna mollis ornare vel eu leo. Donec id elit non mi porta gravida at</p>
            </li>
            <li>
                <h4>This is a frequently asked question</h4>
                <p>Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Nullam quis risus eget urna mollis ornare vel eu leo. Donec id elit non mi porta gravida at</p>
            </li>
        </ul>
    </div>
    
    <div id="form">
        <h3>Company Information</h3>
        <form>
            <div>
                <label for="companyLogo">Upload your <strong>Company Logo</strong></label>
                <input type="file" id="companyLogo" />
            </div>
            <div>
                The following file types/extensions are accepted: 
                <ul>
                    <li>Image/JPEG (.jpg)</li>
                    <li>Image/PNG (.png)</li>
                </ul>
            </div>
            <fieldset>
                <legend>Hiring Manager</legend>
                <label for="firstName">First Name</label>
                <input type="text" id="firstName" name="firstName" class="full" placeholder="First Name" />
                <label for="lastName">Last Name</label>
                <input type="text" id="lastName" name="lastName" class="full" placeholder="Last Name" />
                <label for="name">Email Address</label>
                <input type="text" id="emailAddress" name="emailAddress" class="full" placeholder="Email Address" />
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="full" placeholder="Password" />
                <label for="confirmPassword">Re-Type Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" class="full bottom" placeholder="Re-Type Password" />
            </fieldset>
            <fieldset>
                <legend>Company Name &amp; Location</legend>
                <label for="companyName">Company Name</label>
                <input type="text" id="companyName" name="companyName" class="full" placeholder="Company Name" />
                <label for="address">Address</label>
                <input type="text" id="address" name="address" class="half left" placeholder="Address" />
                <label for="city">City</label>
                <input type="text" id="city" name="city" class="half" placeholder="City" />
                <label for="postal">Postal Code/Zip Code</label>
                <input type="text" id="postal" name="postal" class="half left bottom" placeholder="Postal Code" />
                <label for="country">Country</label>
                <div class="select half bottom">
                    <select name="country" id="country">
                        <option value="38">Canada</option>
                        <option value="213">United States</option>
                    </select>
                </div>
            </fieldset>
            <fieldset>
                <legend>Website &amp; Social Links</legend>
                <label for="website">Website</label>
                <input type="text" id="website" name="website" class="half left" placeholder="Website" />
                <label for="facebook">Facebook</label>
                <input type="text" id="facebook" name="facebook" class="half" placeholder="Facebook" />
                <label for="twitter">Twitter</label>
                <input type="text" id="twitter" name="twitter" class="half left bottom" placeholder="Twitter" />
                <label for="linkedIn">LinkedIn</label>
                <input type="text" id="linkedIn" name="linkedIn" class="half bottom" placeholder="LinkedIn" />
            </fieldset>
<!--            <fieldset>
                <legend>Company Information &amp; Size</legend>
                <label for="businessType">Business Type</label>
                <input type="text" id="businessType" name="businessType" class="half left" placeholder="Business Type" />
                <label for="founded">Founded</label>
                <input type="text" id="founded" name="founded" class="half" placeholder="Founded" />
                <label for="size">Size</label>
                <input type="text" id="size" name="size" class="half left bottom" placeholder="Size" />
                <label for="industry">Industry</label>
                <input type="text" id="industry" name="industry" class="half bottom" placeholder="Industry" />
            </fieldset>
-->
            <fieldset>
                <legend>About The Company</legend>
                <label for="aboutCompany">About The Company</label>
                <textarea id="aboutCompany" name="aboutCompany" class="bottom" rows="5"></textarea>
            </fieldset>
            <input type="submit" value="Submit" class="btn" />
        </form>
    </div>
    
</section>