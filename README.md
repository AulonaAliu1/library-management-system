# Library Management System

Ky projekt eshte nje sistem i thjeshte per menaxhimin e nje biblioteke fizike. Projekti eshte punuar me PHP dhe MySQL. Qellimi kryesor eshte te tregoje si mund te funksionoje nje sistem ku ka libra, perdorues, kerkesa per libra dhe huazime.

Te dhenat kryesore ruhen ne databaze MySQL. Ketu perfshihen perdoruesit, librat, kerkesat, huazimet, resetimi i password-it dhe mesazhet e kontaktit.

## Si ta hapesh projektin

Projekti mund te hapet me XAMPP.

Hapat qe perdoren jane:

1. Vendose projektin brenda folderit `htdocs`.
2. Starto Apache dhe MySQL nga XAMPP.
3. Krijo/importo databazen duke perdorur :
   - `database/library_management_system.sql` per strukture dhe te dhena testuese.
4. Hape kete link ne browser:

```text
http://localhost/library-management-system/public/login.php
```

Nese projekti eshte vendosur ne nje folder tjeter, atehere duhet te ndryshohet edhe linku sipas emrit te folderit.

Konfigurimi i databazes gjendet te `app/config/database.php`. Mund te ndryshohet aty nese MySQL perdor port, username ose password tjeter.

## Cfare permban projekti

Ne projekt ka dy role kryesore:

- admin
- member

Admini ka me shume mundesi ne sistem. Ai mund te menaxhoje librat, te shikoje anetaret, te aprovoje ose te refuzoje kerkesat dhe te menaxhoje huazimet.

Member-i mund te regjistrohet, te beje login, te shikoje librat aktiv, te dergoje kerkese per nje liber dhe te shikoje kerkesat ose huazimet e veta.

## Struktura e projektit

Projekti eshte ndare ne disa foldera kryesore:

- `public` - permban faqet qe hapen direkt nga browseri, si login, register, logout dhe disa API endpoint;
- `app/pages` - permban faqet kryesore te sistemit, si dashboard, books, requests dhe borrowings;
- `app/services` - permban logjiken kryesore per autentikim, libra, perdorues, kerkesa dhe huazime;
- `app/repositories` - permban komunikimin me databazen MySQL;
- `app/classes` - permban klasat si `Book`, `User`, `Admin` dhe `Member`;
- `app/core` - permban lidhjen kryesore me databazen;
- `app/helpers` - permban funksione ndihmese per login, role, redirect, session, validim dhe siguri;
- `assets/css` dhe `assets/js` - permbajne stilizimin dhe JavaScript-in e faqeve;
- `database` dhe `sql` - permbajne skedaret SQL per krijimin dhe mbushjen e databazes;
- `uploads/books` - permban imazhet e librave.

Kjo ndarje eshte bere qe projekti te mos jete i gjithi ne nje skedar dhe te jete me i lehte per t'u kuptuar.

## Login, register dhe logout

Perdoruesi duhet te beje login para se te hyje ne faqet kryesore te sistemit. Pas login-it, te dhenat e perdoruesit ruhen ne session dhe sistemi e di nese perdoruesi eshte admin apo member.

Vizitoret mund te krijojne llogari te re si member nga faqja Register. Admini gjithashtu mund te krijoje anetare nga faqja Members.

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

Nese nje member eshte deaktivizuar nga admini, ai nuk mund te beje login ose reset password derisa admini ta aktivizoje perseri.

## Dashboard

Pas login-it, perdoruesi shkon ne dashboard. Dashboard-i ndryshon sipas rolit.

Admini sheh nje permbledhje me numrin e perdoruesve, librave, kerkesave dhe huazimeve aktive.

Member-i sheh sa kerkesa ka bere dhe sa libra ka aktualisht te huazuar.

Per adminin, dashboard-i perdoret si nje pamje e shkurter e gjendjes se sistemit. Ai mund te kuptoje shpejt sa perdorues jane ne sistem, sa libra jane regjistruar, sa kerkesa jane bere dhe sa huazime jane ende aktive.

Per member-in, dashboard-i eshte me personal. Ai nuk sheh te dhenat e te gjithe sistemit, por vetem informacionet qe lidhen me llogarine e tij. Kjo eshte bere qe cdo rol te shohe vetem informacionet qe i duhen.

## Librat

Faqja e librave shfaq listen e librave qe jane ne sistem. Librat mund te kerkohen, te filtrohen dhe te renditen sipas disa kritereve, si titulli, autori, kategoria ose disponueshmeria.

Admini mund te shtoje libra te rinj, te ndryshoje te dhenat e librave, te perditesoje sasine dhe te arkivoje libra. Librat e arkivuar nuk shfaqen per member-at, por mbeten ne sistem qe historia e kerkesave dhe huazimeve te mos humbe. Admini mund t'i aktivizoje perseri librat e arkivuar.

Member-i mund te beje request per nje liber nese libri eshte aktiv dhe ka kopje te disponueshme. Member-i nuk mund ta huazoje librin direkt nga faqja e librave.

Per cdo liber ruhen disa te dhena kryesore:

- titulli i librit;
- autori;
- kategoria;
- pershkrimi;
- ISBN;
- sasia totale;
- sasia e disponueshme;
- sasia e huazuar;
- statusi i librit;
- imazhi i kopertines.

Search perdoret kur perdoruesi deshiron te gjeje nje liber me shpejt. Ai mund te kerkoje sipas titullit, autorit, kategorise, pershkrimit ose ISBN-se.

Filter perdoret per te shfaqur vetem librat e nje kategorie te caktuar. Per shembull, mund te shfaqen vetem librat e kategorise Programming ose Database.

Sort perdoret per t'i renditur librat. Librat mund te renditen sipas titullit, autorit, kategorise ose sasise se librave te disponueshem.

Kur admini shton ose ndryshon nje liber, sistemi i ruan ndryshimet ne tabelen `books` ne MySQL. Kur member-i ben request per nje liber, krijohet nje kerkese me status `pending`, pastaj admini vendos nese do ta aprovoje apo ta refuzoje.

## Kerkesat

Kur nje member ben request per nje liber, kerkesa ruhet ne databaze me status `pending`. Pastaj admini mund ta aprovoje ose ta refuzoje.

Nese kerkesa aprovohet, krijohet nje huazim i ri. Nese refuzohet, kerkesa mbetet me status `rejected`.

Kerkesat perdoren per ta bere procesin e huazimit me te kontrolluar. Member-i nuk e merr librin menjehere, por dergon nje kerkese. Pastaj admini kontrollon nese libri mund te huazohet dhe merr vendimin.

Statuset kryesore te kerkesave jane:

- `pending` - kerkesa eshte derguar, por admini nuk e ka kontrolluar ende;
- `approved` - admini e ka pranuar kerkesen;
- `rejected` - admini e ka refuzuar kerkesen.

Admini mund te shikoje te gjitha kerkesat nga te gjithe perdoruesit. Member-i mund te shikoje vetem kerkesat e veta.

Kur admini aprovon nje kerkese, sistemi krijon automatikisht nje huazim te ri dhe e lidh ate me perdoruesin dhe librin perkates. Sistemi gjithashtu ul sasine e disponueshme te librit dhe rrit sasine e huazuar.

Member-i nuk mund te dergoje kerkese te dyte per te njejtin liber nese ka tashme nje kerkese `pending` ose nje huazim `active` per ate liber.

## Huazimet

Huazimet tregojne librat qe jane marre nga perdoruesit. Cdo huazim ka daten e huazimit, daten kur libri duhet te kthehet dhe statusin.

Ne databaze fusha quhet `return_date`, por ne faqe shfaqet si due date, sepse tregon daten kur libri duhet te kthehet. Sistemi e vendos automatikisht kete date 14 dite pas aprovimit te kerkeses.

Kur libri eshte ende te perdoruesi, statusi eshte `active`. Kur libri kthehet fizikisht ne biblioteke, admini e konfirmon kthimin dhe sistemi e shenon huazimin si `returned`.

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

Member-i mund te shikoje huazimet dhe due date, por nuk mund ta shenoje vet librin si te kthyer. Vetem admini mund ta konfirmoje kthimin nga faqja Borrowings.

Kur admini e shenon nje liber si te kthyer, sistemi perditeson edhe sasine e librit. Sasia e disponueshme rritet, ndersa sasia e huazuar ulet. Kjo ndihmon qe gjendja e librave te jete me e sakte.

## Profile dhe settings

Ne faqen Profile, perdoruesi mund te ndryshoje emrin, username-in dhe email-in. Ka validim per te kontrolluar nese te dhenat jane ne format te pranueshem dhe nese username/email nuk jane duke u perdorur nga nje perdorues tjeter.

Ndryshimet e profilit ruhen ne MySQL dhe perditesohen edhe ne session per perdoruesin aktual.

Ne Settings, perdoruesi mund te ndryshoje disa preferenca, si theme dhe madhesia e fontit. Keto ruhen me cookies. Settings nuk ndryshon te dhenat kryesore te perdoruesit, por vetem menyren si perdoruesi e sheh faqen.

## Members

Faqja Members eshte vetem per adminin. Aty admini mund te shikoje member-at qe ekzistojne ne sistem, t'i krijoje, t'i editoje, t'i deaktivizoje dhe t'i aktivizoje perseri.

Kjo faqe nuk eshte e hapur per member-at, sepse member-at nuk duhet te kene akses ne listen e plote te perdoruesve.

Member-at nuk fshihen fizikisht nga databaza. Kur admini zgjedh Deactivate, statusi i member-it behet `inactive`. Kjo ruan historine e kerkesave dhe huazimeve. Nese duhet, admini mund ta aktivizoje perseri member-in.

## Si ruhen te dhenat

Te dhenat ruhen ne MySQL.

Tabelat kryesore jane:

- `users` - permban perdoruesit, rolin, statusin dhe password-in e ruajtur me hash;
- `books` - permban librat, sasite, statusin dhe imazhin;
- `requests` - permban kerkesat per libra;
- `borrowings` - permban huazimet e librave;
- `password_resets` - permban token-at per reset password;
- `contact_messages` - permban mesazhet e kontaktit.
