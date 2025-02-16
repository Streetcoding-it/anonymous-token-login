Di seguito trovi un documento riepilogativo che descrive i passaggi base per la creazione di un modulo in Drupal 10/11, includendo una spiegazione dei concetti di hook e di routing.

---

# Creazione di un Modulo in Drupal 10/11: Riepilogo e Concetti Chiave

## Introduzione

Un modulo in Drupal permette di estendere e personalizzare le funzionalità del core. Utilizzando moduli personalizzati puoi aggiungere nuove funzionalità o modificare il comportamento predefinito di Drupal senza alterare il core. In questo documento vedremo i passaggi base per la creazione di un modulo e approfondiremo due concetti fondamentali: gli hook e il sistema di routing.

## Passaggi Base per Creare un Modulo

1. **Creare la Cartella del Modulo:**
   - Posiziona il modulo nella cartella `modules/custom` del tuo sito Drupal.
   - Ad esempio: `modules/custom/mio_modulo`.

2. **Creare il File .info.yml:**
   - Questo file contiene i metadati del modulo (nome, descrizione, versione, compatibilità con il core).
   - Esempio:
     ```yaml
     name: "Mio Modulo Personalizzato"
     type: module
     description: "Descrizione del modulo personalizzato per Drupal 10/11."
     core_version_requirement: ^10 || ^11
     package: Custom
     version: "1.0"
     ```

3. **Creare il File .module:**
   - In questo file inserirai il codice PHP per implementare la logica del modulo.
   - Qui potrai utilizzare gli hook per intervenire nel comportamento di Drupal (vedi sezione successiva).

4. **Definire il Routing:**
   - Crea un file di routing, ad esempio `mio_modulo.routing.yml`, per definire le URL e i relativi controller.
   - Esempio:
     ```yaml
     mio_modulo.example:
       path: '/mio-modulo/esempio'
       defaults:
         _controller: '\Drupal\mio_modulo\Controller\ExampleController::content'
         _title: 'Pagina di Esempio'
       requirements:
         _permission: 'access content'
     ```

5. **Creare Eventuali Controller o Altri Componenti:**
   - Se il modulo gestisce pagine o logiche complesse, crea una cartella `src/Controller` e definisci i controller necessari.
   - Un controller in Drupal è una classe PHP che restituisce la risposta HTTP per una determinata route.

## Concetti Fondamentali

### Gli Hook

- **Definizione:**  
  Gli hook sono funzioni speciali che consentono ai moduli di "agganciarsi" ad eventi o flussi di dati del sistema. Drupal invoca automaticamente tutte le implementazioni di un determinato hook, consentendo a più moduli di intervenire sullo stesso processo.
  
- **Esempio:**  
  - `hook_user_insert()`: Consente di eseguire del codice subito dopo la creazione di un nuovo utente.
  - **Utilizzo:** Se desideri, per esempio, generare un token o inviare una notifica alla registrazione, puoi implementare questo hook nel tuo file `.module`.

- **Vantaggi:**  
  - Permettono una personalizzazione estesa senza modificare il core.
  - Favoriscono l’interoperabilità tra moduli, poiché ogni modulo può aggiungere o modificare comportamenti esistenti.

### Il Sistema di Routing

- **Definizione:**  
  Il routing in Drupal è il meccanismo che mappa le URL richieste dall’utente a specifici controller o callback. Questo sistema, basato sul componente Symfony Routing, consente di definire in modo dichiarativo le rotte per il modulo.

- **Struttura del File di Routing:**  
  - Il file YAML (ad esempio, `mio_modulo.routing.yml`) contiene le definizioni delle rotte.
  - Ogni rotta specifica:
    - **path:** La URL che l’utente dovrà visitare.
    - **defaults:** Il controller che gestirà la richiesta e il titolo della pagina.
    - **requirements:** I permessi necessari per accedere a quella rotta.

- **Esempio Pratico:**  
  ```yaml
  mio_modulo.example:
    path: '/mio-modulo/esempio'
    defaults:
      _controller: '\Drupal\mio_modulo\Controller\ExampleController::content'
      _title: 'Pagina di Esempio'
    requirements:
      _permission: 'access content'
  ```
  In questo esempio, quando l’utente visita `/mio-modulo/esempio`, Drupal invoca il metodo `content` della classe `ExampleController`, mostrando la pagina definita.

## Conclusioni

Per creare un modulo in Drupal 10/11:
- Crea la struttura di base nella cartella `modules/custom`.
- Definisci i metadati nel file `.info.yml`.
- Aggiungi la logica del modulo nel file `.module` utilizzando gli hook per intervenire nei processi di Drupal.
- Imposta le rotte per le pagine o le funzionalità del modulo nel file di routing, mappando le URL ai controller.

Questi passaggi e concetti sono fondamentali per sviluppare moduli efficaci e personalizzati in Drupal, sfruttando appieno il sistema di hook e il potente meccanismo di routing basato su Symfony.

---
