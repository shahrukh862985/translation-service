<?php

namespace Database\Factories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translation>
 */
class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    protected $keySections = [
        "home",
        "aboutthersf",
        "aboutus",
        "mediacenter",
        "news",
        "relatedlinks",
        "contactus",
        "ministryofdefence",
        "ministryofjustice",
        "therepublicanpalace",
        "khartoumstatepolice",
        "photogallery",
        "videogallery",
        "issues",
        "reports",
        "dialogues",
        "whoarewe",
        "rapidsupportforces",
        "strategictasks",
        "samplesofbattlefieldvictories",
        "projects",
        "objectives",
        "tasksandduties",
        "generalfeaturesofthersface",
        "theswearin",
        "theslogan",
        "aphototourwiththerapidsupportforces",
        "mainheading",
        "paginationnext",
        "paginationprevious",
        "readmore",
        "showmore",
        "viewalbum",
        "latestnews",
        "abouttherapidsupportforces",
        "showless",
        "faqs",
        "copyright",
        "importantlinks",
        "otherlinks",
        "footerabout",
        "abouttherapidinterventionforces",
        "historyoftherapidinterventionforces",
        "sitemap",
        "newsbulletins",
        "morenews",
        "howcanwehelpyou",
        "subscribebyemail",
        "stayinformedofthelatesnews",
        "writeyouremailhere",
        "subscribe",
        "contactinformation",
        "contacttext",
        "firstname",
        "lastname",
        "email",
        "mobilenumber",
        "messagetext",
        "sendthemessage",
        "seealltheworkoftherapidsupportforcesthroughpictures",
        "seealltheworkoftherapidsupportforcesthroughvideos",
        "thereissomeerrorpleasetryagain",
        "therequestwassentsuccessfully",
        "emailisrequired",
        "pleaseentervalidemailaddress",
        "firstnameisrequired",
        "lastnameisrequired",
        "phoneisrequired",
        "messageisrequired",
        "share",
        "watch",
        "tags",
        "newsdetails",
        "similarnews",
        "themostimportnewsandinternationaltopicsoftherapidsupportforces",
        "more",
        "all",
        "internationalnews",
        "localnews",
        "trainingnews",
        "downloadtheversion",
        "download",
        "nonewsfound",
        "whatareyousearchingfor",
        "noresultswerefoundmatchingthewords",
        "searchresults",
        "videolibrary",
        "mission",
        "confrontingtheillegralmigration",
        "firearmscolection",
        "communityprotection",
        "protectingtheborders",
        "protectingtheeconomy",
        "settlementofthenomads",
        "thevoluntaryreturn",
        "version",
        "interview",
        "invalidcaptchaauthenticationpleasetryagain"
    ];

    public function definition()
    {
        $keySection1 = $this->faker->randomElement($this->keySections);
        $keySection2 = $this->faker->randomElement($this->keySections);
        $key = ucwords($this->faker->word()) . $keySection1 . ucwords($this->faker->word()) . ucwords($this->faker->word()) . $keySection2;

        return [
            'key' => $key,
            'content' => $this->faker->paragraph(),
        ];
    }
}
