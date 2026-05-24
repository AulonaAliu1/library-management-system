# Library Management System

Ky projekt eshte nje sistem i thjeshte per menaxhimin e nje biblioteke. Projekti eshte punuar me PHP dhe perdor disa skedare me te dhena te ruajtura ne array, prandaj ende nuk perdor databaze. Qellimi kryesor eshte te tregoje si mund te funksionoje nje sistem ku ka libra, perdorues, kerkesa per libra dhe huazime.

Projekti eshte menduar si version fillestar, ku te dhenat jane dummy data. Kjo do te thote qe perdoruesit, librat, kerkesat dhe huazimet jane vendosur paraprakisht ne skedare, ne menyre qe sistemi te testohet pa pasur nevoje per databaze.

## Si ta hapesh projektin

Projekti mund te hapet me XAMPP.

Hapat qe perdoren jane:

1. Vendose projektin brenda folderit `htdocs`.
2. Starto Apache nga XAMPP.
3. Hape kete link ne browser:

```text
http://localhost/library-management-system/public/login.php
```

Nese projekti eshte vendosur ne nje folder tjeter, atehere duhet te ndryshohet edhe linku sipas emrit te folderit.

## Cfare permban projekti

Ne projekt ka dy role kryesore:

- admin
- member

Admini ka me shume mundesi ne sistem. Ai mund te menaxhoje librat, te shikoje anetaret, te aprovoje ose te refuzoje kerkesat dhe te menaxhoje huazimet.

Member-i mund te shikoje librat, te kerkoje libra, te beje request per nje liber dhe te shikoje kerkesat ose huazimet e veta.

## Struktura e projektit

Projekti eshte ndare ne disa foldera kryesore:

- `public` - permban faqet qe hapen direkt nga browseri, si login dhe logout;
- `app/pages` - permban faqet kryesore te sistemit, si dashboard, books, requests dhe borrowings;
- `app/services` - permban logjiken kryesore per autentikim, libra, perdorues dhe kerkesa;
- `app/classes` - permban klasat si `Book`, `User`, `Admin` dhe `Member`;
- `app/data` - permban dummy data qe perdoren nga projekti;
- `app/helpers` - permban funksione ndihmese per login, role, redirect dhe session;
- `assets/css` - permban stilizimin e faqeve;
- `docs` - permban dokumentim shtese per projektin.

Kjo ndarje eshte bere qe projekti te mos jete i gjithi ne nje skedar dhe te jete me i lehte per t'u kuptuar.

## Login dhe logout

Perdoruesi duhet te beje login para se te hyje ne faqet kryesore te sistemit. Pas login-it, te dhenat e perdoruesit ruhen ne session dhe sistemi e di nese perdoruesi eshte admin apo member.

Kur perdoruesi ben logout, session-i pastrohet dhe ai kthehet perseri te faqja e login-it.

Per testim mund te perdoren keto llogari:

| Roli | Username | Email | Password |
| --- | --- | --- | --- |
| admin | Admin | admin@library.local | admin123 |
| member | Aulona | aulona@library.local | password123 |
| member | Eliza | eliza@library.local | password123 |
| member | Erdoart | erdoart@library.local | password123 |
| member | Lindrit | lindrit@library.local | password123 |

Login mund te behet ose me username ose me email. Per shembull, per admin mund te perdoret `Admin` ose `admin@library.local`, bashke me password-in `admin123`.

## Dashboard

Pas login-it, perdoruesi shkon ne dashboard. Dashboard-i ndryshon sipas rolit.

Admini sheh nje permbledhje me numrin e perdoruesve, librave, kerkesave dhe huazimeve aktive.

Member-i sheh sa kerkesa ka bere dhe sa libra ka aktualisht te huazuar.

Per adminin, dashboard-i perdoret si nje pamje e shkurter e gjendjes se sistemit. Ai mund te kuptoje shpejt sa perdorues jane ne sistem, sa libra jane regjistruar, sa kerkesa jane bere dhe sa huazime jane ende aktive.

Per member-in, dashboard-i eshte me personal. Ai nuk sheh te dhenat e te gjithe sistemit, por vetem informacionet qe lidhen me llogarine e tij. Kjo eshte bere qe cdo rol te shohe vetem informacionet qe i duhen.

## Librat

Faqja e librave shfaq listen e librave qe jane ne sistem. Librat mund te kerkohen, te filtrohen dhe te renditen sipas disa kritereve, si titulli, autori, kategoria ose disponueshmeria.

Admini mund te shtoje libra te rinj, te ndryshoje te dhenat e librave, te perditesoje sasine dhe te fshije libra.

Member-i mund te beje request per nje liber nese libri ka kopje te disponueshme.

Per cdo liber ruhen disa te dhena kryesore:

- titulli i librit;
- autori;
- kategoria;
- pershkrimi;
- ISBN;
- sasia totale;
- sasia e disponueshme;
- sasia e huazuar.

Search perdoret kur perdoruesi deshiron te gjeje nje liber me shpejt. Ai mund te kerkoje sipas titullit, autorit, kategorise, pershkrimit ose ISBN-se.

Filter perdoret per te shfaqur vetem librat e nje kategorie te caktuar. Per shembull, mund te shfaqen vetem librat e kategorise Programming ose Database.

Sort perdoret per t'i renditur librat. Librat mund te renditen sipas titullit, autorit, kategorise ose sasise se librave te disponueshem.

Kur admini shton nje liber te ri, sistemi e ruan ate ne `books-data.php`. Kur admini ndryshon sasine, sistemi llogarit perseri sa kopje jane te disponueshme duke zbritur kopjet qe jane aktualisht te huazuara.

Kur member-i ben request per nje liber, libri nuk huazohet direkt. Fillimisht krijohet nje kerkese me status `pending`, pastaj admini vendos nese do ta aprovoje apo ta refuzoje.

## Kerkesat

Kur nje member ben request per nje liber, kerkesa ruhet me status `pending`. Pastaj admini mund ta aprovoje ose ta refuzoje.

Nese kerkesa aprovohet, krijohet nje huazim i ri. Nese refuzohet, kerkesa mbetet me status `rejected`.

Kerkesat perdoren per ta bere procesin e huazimit me te kontrolluar. Member-i nuk e merr librin menjehere, por dergon nje kerkese. Pastaj admini kontrollon nese libri mund te huazohet dhe merr vendimin.

Statuset kryesore te kerkesave jane:

- `pending` - kerkesa eshte derguar, por admini nuk e ka kontrolluar ende;
- `approved` - admini e ka pranuar kerkesen;
- `rejected` - admini e ka refuzuar kerkesen.

Admini mund te shikoje te gjitha kerkesat nga te gjithe perdoruesit. Member-i mund te shikoje vetem kerkesat e veta.

Kur admini aprovon nje kerkese, sistemi krijon automatikisht nje huazim te ri dhe e lidh ate me perdoruesin dhe librin perkates.

## Huazimet

Huazimet tregojne librat qe jane marre nga perdoruesit. Cdo huazim ka daten e huazimit, daten e kthimit dhe statusin.

Kur libri eshte ende te perdoruesi, statusi eshte `active`. Kur libri kthehet, admini mund ta shenoje si `returned`.

Huazimet krijohen pasi nje kerkese aprovohet nga admini. Kjo tregon qe libri nuk eshte vetem i kerkuar, por eshte dhene realisht te perdoruesi.

Per cdo huazim ruhen keto te dhena:

- perdoruesi qe ka huazuar librin;
- libri qe eshte huazuar;
- data e huazimit;
- data kur duhet te kthehet libri;
- statusi i huazimit.

Statuset kryesore te huazimeve jane:

- `active` - libri eshte ende i huazuar;
- `returned` - libri eshte kthyer.

Kur admini e shenon nje liber si te kthyer, sistemi perditeson edhe sasine e librit. Sasia e disponueshme rritet, ndersa sasia e huazuar ulet. Kjo ndihmon qe gjendja e librave te jete me e sakte.

## Profile dhe settings

Ne faqen Profile, perdoruesi mund te ndryshoje username-in dhe email-in. Ka validim te thjeshte per te kontrolluar nese te dhenat jane ne format te pranueshem.

Ne Settings, perdoruesi mund te ndryshoje disa preferenca, si theme dhe madhesia e fontit. Keto ruhen me cookies.

Te Profile, sistemi kontrollon username-in dhe email-in para se t'i ruaje ndryshimet. Username-i nuk duhet te jete bosh, nuk duhet te jete vetem me numra dhe nuk duhet te kete karaktere te palejuara. Email-i duhet te kete format normal email-i.

Ndryshimet e profilit ruhen ne session per perdoruesin aktual. Kjo do te thote qe ndryshimi shihet gjate kohes qe perdoruesi eshte i kycur.

Te Settings, preferencat ruhen me cookies. Kjo perdoret qe zgjedhjet si theme ose madhesia e fontit te mos humbin menjehere pasi faqja rifreskohet.

Settings nuk ndryshon te dhenat kryesore te perdoruesit, por vetem menyren si perdoruesi e sheh faqen.

## Members

Faqja Members eshte vetem per adminin. Aty admini mund te shikoje perdoruesit qe ekzistojne ne sistem dhe rolin e secilit.

Kjo faqe nuk eshte e hapur per member-at, sepse member-at nuk duhet te kene akses ne listen e plote te perdoruesve.

Ne kete projekt, perdoruesit merren nga `users-data.php`. Admini mund t'i shohe ata si liste me username dhe role.

Kjo pjese ndihmon per te treguar si funksionon kontrolli i roleve. Edhe pse nje member eshte i loguar, ai nuk mund te hyje ne cdo faqe. Disa faqe jane vetem per admin.

## Si ruhen te dhenat

Te dhenat ruhen ne skedare PHP:

- `users-data.php`
- `books-data.php`
- `requests-data.php`
- `borrowings-data.php`

`users-data.php` permban perdoruesit dummy qe perdoren per login. Aty ruhen username-i, email-i, roli dhe password-i per secilin perdorues.

`books-data.php` permban listen e librave. Secili liber ka titull, autor, kategori, pershkrim, ISBN, sasi totale, sasi te disponueshme dhe sasi te huazuar.

`requests-data.php` permban kerkesat qe kane bere member-at per libra. Kerkesat mund te jene me status `pending`, `approved` ose `rejected`.

`borrowings-data.php` permban huazimet e librave. Nje huazim mund te jete `active` kur libri eshte ende i huazuar, ose `returned` kur libri eshte kthyer.

 