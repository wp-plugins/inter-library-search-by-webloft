=== ILS Search by Webloft ===
Contributors: sundaune
Tags: bibliotek, katalog, inter library search, search, søk, søking, søkemotor, bibliotekkatalog, bibliofil, bibsys, koha, metasøk, library, bibvenn, webekspertene, webløft, webloft, e-bok, ebok, e-book, e-books, bibliotekarens beste venn
Requires at least: 3.0
Tested up to: 4.2
Stable tag: 2.4.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Search different library catalogs from within your own site. Norwegian: Søk i bibliotekets katalog uten å forlate din egen hjemmeside.

== Description ==

This plugin enables a shortcode that lets you insert a search form to search the library catalogue of your choice - and the hits are displayed inline in your own site. You also have the option to display single posts on the page or post of your liking, as well as inserting the search form in the form of a widget anywhere on your site and sending the results to the post or page you prefer.

Custom options include whether to search for book covers and check item availability on the fly. The output is easily customizable (if you know CSS) by editing the included style sheets.

The plugin currently supports Bibsys, Bibliofil, Koha and Tidemann. It also support searching free online books from the National Library (Bokhylla).

NORWEGIAN:

Dette innstikket installerer en kortkode som lar deg sette inn et søkeskjema som søker i en valgfri bibliotekkatalog - og viser treffene inne i din egen hjemmeside. Du kan altså vise enkeltposter innbakt i en side eller et innlegg som du velger selv, og du kan sette inn søkeskjemaet som en widget hvor som helst på siden din - og sende treffene til en hvilken som helst side eller innlegg!

Innstillingene inkluderer muligheten for å finne bokomslag forskjellige steder og å sjekke bestand for hver bok før treffene vises. Utseendet kan enkelt tilpasses (hvis du kan CSS) ved å redigere de medfølgende stilarkene.

Innstikket har for tiden støtte for Bibsys, Bibliofil, Koha og Tidemann. Det støtter også oppslag i gratis tilgjengelige bøker på nett fra Nasjonalbiblioteket (Bokhylla)

== Installation ==

= Uploading the plugin via the Wordpress control panel =

Make sure you have downloaded the .zip file containing the plugin. Then:

1. Go to 'Add' on the plugin administration panel
2. Proceed to 'Upload'
3. Choose the .zip file on your local drive containing the plugin
4. Click 'Install now'
5. Activate the plugin from the control panel

= Upload the plugin via FTP =

Make sure you have downloaded the .zip file containing the plugin. Then:

1. Unzip the folder 'webloft-ils' to your local drive
2. Upload the folder 'webloft-ils' to the '/wp-content/plugins/' folder (or wherever you store your plugins)
3. Activate the plugin from the control panel

... Or install it via the Wordpress repository!

= Usage =

To use the search function in the simplest way, just insert the following shortcode in a post/page:

[wl-ils]

The search form will then be inserted, and search the library catalogue you have choosen on the settings page. If you want this particular search form to use another catalogue (this way you can have search forms for several libraries on your page), just use:

[wl-ils mittbibliotek=CODE]

... where CODE is taken from the table of codes available from the settings page. When the user enters a search terms and hits Enter or clicks OK, the results will appear directly beneath in an automatically expanding iframe. You can also display the search form as a widget in widgetized areas of your site (side bars, header, footer etc.). In this case you have to choose which page or post to send the results to. You do this from the widget settings - Webloft ILS will automatically scan all posts and pages, and let you choose from the ones containing the [wl-ils] shortcode.

For even more hardcore control, you can specify directly in the shortcode which post/page to display your results on - just do this:

[wl-ils trefflisteside=ID]

... where ID is the id (doh!) of the post/page you want to display your results on.

Display of single items

If you do not want your users to exit your site and be taken to the library system's own pages when clicking a single hit, you can display single items on a post/page of your liking. To do this, such a post/page much exist and contain the shortcode:

[wl-ils-enkeltpost]

You must also activate the setting on the plugin's settings page - Webloft ILS will scan all posts/pages and let you choose from the ones containing the [wl-ils-enkeltpost] shortcode. When this option is activated, your users will be taken to this post/page when clicking an item in the result list, and the shortcode on that page will be replaced with information about the item as well as availability information and links to reserve, order or read the item online where applicable. The URL to this page/post (allthough preposterously long!) can be used as a direct link this item.

NORWEGIAN:

= Laste opp innstikket i kontrollpanelet for Wordpress =

Sørg for at du har lastet ned ZIP-filen som inneholder innstikket. Deretter:

1. Gå til 'Legg til' på administrasjonssiden for innstikk
2. Gå til 'Last opp'
3. Velg ZIP-filen som inneholder innstikket på harddisken din
4. Klikk 'Installer nå'
5. Aktiver innstikket fra kontrollpanelet

= Laste opp innstikket via FTP =

Sørg for at du har lastet ned ZIP-filen som inneholder innstikket. Deretter:

1. Pakk ut mappen 'webloft-ils' til datamaskinen din
2. Last opp mappen 'webloft-ils' til '/wp-content/plugins/'-katalogen under din Wordpress-installasjon
3. Aktiver innstikket fra kontrollpanelet

--- Eller installér det via Wordpress-katalogen!

= Bruk =

For å bruke søkeskjemaet på enkelste måte kan du bare sette inn følgende kortkode på en side eller i et innlegg:

[wl-ils]

Da vil et søkeskjema bli satt inn, som søker i den bibliotekkatalogen du har angitt i innstillingene i Wordpress. Hvis du vil at dette søkeskjemaet skal søke i et annet biblioteks katalog (på denne måten kan du ha søkebokser til flere forskjellige biblioteker på nettsidene dine) kan du bare sette inn:

[wl-ils mittbibliotek=KODE]

... hvor KODE er hentet fra tabellen med bibliotekkoder som er tilgjengelig fra siden med innstillinger. Når brukeren skriver inn en søketerm og trykker Enter eller klikker OK vil resultatene vises rett under, i en iframe som ekspanderer automatisk til antallet treff.

Vil du være enda mer avansert kan du angi direkte i shortcode hvilken side/innlegg trefflisten skal vises på, ved å gjøre dette:

[wl-ils trefflisteside=ID]

... hvor ID er id-en til det innlegget eller den siden du vil vise trefflisten på. 

Du kan også vise søkeskjemaet som en widget hvis nettstedet ditt har støtte for slike (på siden, på toppen, i bunnen osv.). Da må du i tilfelle angi hvilken side eller hvilket innlegg resultatene skal sendes til. Dete gjøres i widgetens innstillinger - Webloft ILS vil automatisk lete gjennom alle sider og innlegg for å la deg velge blant de som inneholder kortkoden [wl-ils].

Visning av enkeltposter

Hvis du ikke vil sende brukerne ut av siden din når de klikker på et treff, kan du velge å vise enkeltposter inne i nettstedet ditt. For å gjøre det må du først opprette et innlegg eller en side som inneholder kortkoden:

[wl-ils-enkeltpost]

Du må også aktivere denne innstillingen på innstikkets innstillingsside - Webloft ILS vil lete gjennom alle sider/innlegg og la deg velge blant dem som inneholder kortkoden [wl-ils-enkeltpost]. Når denne innstillingen er påslått vil brukerne tas til denne siden eller dette innlegget når de klikker på et treff, og kortkoden vil her bli erstattet av en faktaboks med informasjon om treffet såvel som bestand, lenker til reservering, bestilling og online-tilgang der dette finnes. URL-en til denne siden kan (selv om den er forferdelig lang!) deles som en direkte lenke til denne posten.

== Frequently Asked Questions ==

= Why are there no frequently asked questions? =

Because the plugin is so new that no question has had the chance to become frequent yet!

NORWEGIAN:

= Hvorfor er det ingen Ofte Stilte Spørsmål? =

Fordi dette er første versjon - spørsmålene kommer nok etter hvert.

== Screenshots ==

1. Search form with results
2. Display of single item

NORWEGIAN:

1. Søkeskjemaet med trefflisten under
2. Visning av enkeltpost

== Change log ==

= 2.4.2 = 

* Bugfix: Failed to load CSS when only widget was present in page (thank you, Nikolaj Blegvad!)
* Bugfix: Failed to display book cover when multiple URNs encountered in Bokhylla

= 2.4.1 = 

* Merely cosmetics

= 2.4 =

* Can now send results to any premade post/page directly using shortcode argument
* Can now pick a background color, text color and rounded edges for the submit button in all widgets
* Option to include a link to the lbrary system's advanced search screen on result page

= 2.3.1 = 

* Bugfix: Widget wasn't keeping the search term

= 2.3 = 

* Feature: Now possible to find free books available online from the National Library (bokhylla.no)

= 2.2 =

* Bugfix: Widget now works with displaying single post on its own page
* Cleanup: Functions code no longer conflicts with other addons
* Visual: Fixed social media icons for sharing single posts

= 2.1.1 =

* Should no longer require the PEAR_Exception class

= 2.1 =

* Code rewrite to simplify a bit
* Responsive layout for mobile phones and tablets

= 2.0.4 = 

* Bugfix: Fixed Moss bibliotek
* New library: Nedre Eiker bibliotek

= 2.0.3 =

* Bugfix: Widget redirecting search to result page didn't work

= 2.0.2 =

* Widget: Support for choosing the library catalogue to search in on a per-widget basis
* Shortcode: Support for specifying the library catalogue in the shortcode via the "mittbibliotek" parameter
* Now fetches PDF excerpt from MARC 856 where available
* Bugfix: Wrong character encoding in Facebook sharer window

= 2.0.1 =

* Initial public release
* Support for Tidemann libraries added
* Bugfix: Description in Facebook share window was empty

= 1.3 =

* Bugfix: Widget wasn't passing search query along to the search page
* Feature: Added Twitter and Facebook share buttons on single items
* Feature: Now possible to link directly to single items from the outside
* Bugfix: URI sometimes too long for browser when sending availability data in the query string (414 error)

= 1.2 =

* A LOT of additional info on each item (020$b,082$a,100$d,245$c,300$a/b,500$a,505$a,511$a,574$a,650$a,740$a etc.)
* Tab based view of single post information
* Code optimalization

= 1.1 =

* Bugfix "costa rica": Pagination when search query contains several words not inside quotes
* Bugfix: Treat search for several words as [word1 AND word2], not OR
* New layout

= 1.0 =

* First version

NORWEGIAN:

= 2.4.2 = 

* Bugfix: Stilark ble ikke lastet når bare widget var til stede på siden (takk, Nikolaj Blegvad!)
* Bugfix: Viste ikke omslagsbilde når boka hadde flere URN i Bokhylla

= 2.4.1 =

* Kun kosmetikk

= 2.4 =

* Kan nå sende trefflisten til en hvilken som helst side/innlegg ved hjelp av argument i shortcode
* Kan nå velge bakgrunnsfarge, tekstfarge og runde kanter på søkeknappen i widget
* Kan inkludere lenke til avansert søk i ditt biblioteksystem på toppen av trefflista

= 2.3.1 = 

* Bugfix: Søketermen ble ikke med fra widget

= 2.3 = 

* Nytt: Nå mulig å søke blant gratis bøker tilgjengelig på nett fra Nasjonalbiblioteket (Bokhylla)

= 2.2 =

* Bugfix: Widget fungerer nå selv når man skal vise enkeltposter på en egen side
* Opprydning: Koden med funksjoner vil ikke havne i konflikt med andre utvidelser
* Visuelt: Fikset ikonene for å dele enkeltposter på sosiale media

= 2.1.1 =

* Skal ikke lenger være avhengig av PEAR_Exception-klassen

= 2.1 =

* Skrevet om koden for å forenkle litt
* Responsivt design, layout tilpasset mobiltelefoner og nettbrett

= 2.0.4 = 

* Bugfix: Fikset Moss bibliotek
* Nytt bibliotek: Nedre Eiker

= 2.0.3 =

* Bugfix: Widget omdirigerte ikke til riktig side for å vise trefflisten

= 2.0.2 =

* Widget: Mulighet for å velge hvilket biblioteks katalog det skal søkes i for hver enkelt widget
* Kortkode: Mulighet for å velge bibliotek-katalog i kortkode ved hjelp av "mittbibliotek"-parameteret
* Henter nå PDF med utdrag fra MARC-felt 856 der dette finnes
* Bugfix: Feil tegnkoding i Facebooks delevindu

= 2.0.1 =

* Første offentlige slipp
* Lagt til støtte for Tidemann
* Bugfix: Beskrivelse kom ikke med i delingsvinduet for Facebook

= 1.3 =

* Bugfix: Søkestrengen ble ikke med over fra widget til søkeside
* Forbedring: Lagt til Twitter- og Facebook-knapper for å dele enkeltposter
* Forbedring: Nå kan du lenke direkte til sider med enkeltposter utenfra
* Bugfix: URI-en ble noen ganger for lang for nettleseren når bestandsdata ble sendt meg URL-en (414-feil)

= 1.2 =

* MYE mer informasjon om hvert enkelt treff (020$b,082$a,100$d,245$c,300$a/b,500$a,505$a,511$a,574$a,650$a,740$a etc.)
* Fanebasert visning av informasjon for enkelttreff
* Kodeoptimalisering

= 1.1 =

* Bugfix "costa rica": Paginering når man søkte på flere ord uten å sette i anførselstegn
* Bugfix: Behandle søk etter flere ord som [ord1 OG ord2], ikke ELLER
* Ny layout

= 1.0 =

* Første versjon

== Upgrade Notice ==

No upgrade notice

NORWEGIAN:

Ingen kommentar til denne versjonen
