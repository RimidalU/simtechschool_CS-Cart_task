
<?php

class FirstCest
{
    public function frontpageWorks(AcceptanceTester $I)
    {
        //home page
        $I->amOnPage('/');
        // login
        $I->Click(['xpath' => '//*[@id="account_info_links_15"]/li[1]/a']);
        $I->fillField(['id' => 'login_main_login'], 'testUser@test.com');
        $I->fillField(['id' => 'psw_main_login'], 'test$1user');
        $I->click(['xpath' => '//*[@id="tygh_main_container"]/div[3]/div/div[2]/div[1]/div/div/div/form/div[3]/div[1]/button']);
        $I->click(['xpath' => '//*[@id="account_info_links_15"]/li[1]/a']);
        $I->Click(['xpath' => '//div[@id="sw_dropdown_5"]/a']);
        $I->see('User Test');
        $I->makeHtmlSnapshot();
        // departments page
        $I->Click(['xpath' => '//*[@id="account_info_links_15"]/li[5]/a']);
        $I->see('First Department');
        $I->see('Мария Родникова');
        $I->see('First Department');
        $I->seeElement('//*[@id="pagination_contents"]/div/div[1]/div/div[1]/a/img');
        $I->makeHtmlSnapshot();
        // definite department
        $I->click(['xpath' => '//*[@id="pagination_contents"]/div/div[1]/div/div[1]/a']);
        $I->see('Анна Петрова');
        $I->see('Екатерина Смирнова');
        $I->see('Ксения Родионова');
        $I->makeHtmlSnapshot();
        // logout
        $I->Click(['xpath' => '//div[@id="sw_dropdown_5"]/a']);
        $I->Click(['xpath' => '//*[@id="account_info_5"]/div[2]/a']);
        // check logout
        $I->Click(['xpath' => '//*[@id="account_info_links_15"]/li[1]/a']);
        $I->see('Not a registered member?');
        $I->makeHtmlSnapshot();
    }
}