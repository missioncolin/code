<a href="/hr-signup" id="priceCallout" class="btn green">First Job is Free! <span>Sign Up Today!</span></a>

<ul id="pricingOptions">
    <li>
        <h2>$<?php echo $db->return_specific_item(false, 'tblJobCreditsPricing', 'price', "199", '`credits` = 1');?></h2>
        <h5>1 Job</h5>
        <p><a class="btn" href="#">Buy Now</a></p>
    </li>
    <li>
        <h2>$<?php echo $db->return_specific_item(false, 'tblJobCreditsPricing', 'price', "550", '`credits` = 3');?></h2>
        <h5>3 Jobs</h5>
        <p><a class="btn" href="#">Buy Now</a></p>
    </li>
    <li>
        <h2>$<?php echo $db->return_specific_item(false, 'tblJobCreditsPricing', 'price', "1500", '`credits` = 10');?></h2>
        <h5>10 Jobs</h5>
        <p><a class="btn" href="#">Buy Now</a></p>
    </li>
</ul>

<div id="about" class="box">
    <h3>About Intervue</h3>
    <p>Intervue is an online recruitment tool that screens and ranks job applicants. No matter where you post your job, your candidates can be automatically routed through Intervue's application process. For every applicant you receive a ranking based on your specific job requirements, recorded web-cam responses to your questions, and the traditional resume and cover letter.  All of this means that you get to know your applicants before you ever interview them. That's how Intervue is helping companies hire faster, hire better and hire smarter. Create your first job today.</p>
</div>

<div id="testimonials">
    <div id="testimonialRotator">
        <div class="testimonialItem">
            <p>Maecenas faucibus mollis interdum. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibl.</p>
            <h4>John Smith<br /><span>Acme, Inc.</span></h4>
        </div>
        <div class="testimonialItem">
            <p>Maecenas faucibus mollis interdum. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibl.</p>
            <h4>Jon Rundle<br /><span>Resolution IM</span></h4>
        </div>
        <div class="testimonialItem">
            <p>Maecenas faucibus mollis interdum. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibl.</p>
            <h4>Joe Smith<br /><span>Company, Inc.</span></h4>
        </div>
    </div>
    <div id="testimonialNav"></div>
</div>