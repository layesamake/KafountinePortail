(() => {
  'use strict';
  const main = document.getElementById('main');
  const nav = document.getElementById('navigation');
  const search = document.getElementById('search');
  const escape = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const all = [...schema.contents, ...schema.options, schema.common];
  const find = id => all.find(x => x.id === id) || schema.taxonomies.find(x => x.id === id);
  const link = (id, label) => `<a class="tag link" href="#${escape(id)}">${escape(label || find(id)?.title || id)}</a>`;
  const flatten = fields => fields.flatMap(x => [x,...flatten(x.children || [])]);
  const fieldCount = fields => flatten(fields).length;
  const groupCount = all.length;
  const totalFields = all.reduce((n,x) => n + fieldCount(x.fields),0);
  const normalize = value => String(value).normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();
  const groupKey = item => item.group;
  const keyFor = (owner, field, parent = '') => owner.id === 'commun' ? field.name : `${parent || owner.id}_${field.name}`;
  const isOption = item => schema.options.some(x => x.id === item.id);
  const knownRoutes = new Set(['overview','taxonomies','options','relations','livraison',...all.map(x=>x.id),...schema.taxonomies.map(x=>x.id)]);
  const amount = schema.contents.find(x=>x.id==='ck_demarche').fields.find(x=>x.name==='montant_fcfa');
  amount.requiredWhen = 'Si le coût est un montant fixe';
  function header(kicker, title, description) {
    return `<div class="breadcrumbs"><a href="#overview">Plan ACF PRO</a><span>/</span>${escape(kicker)}</div><p class="intro-label">${escape(kicker)}</p><h1>${escape(title)}</h1><p class="lead">${escape(description)}</p>`;
  }
  function choices(field) {
    return field.choices ? `<div class="choices">${field.choices.map(x=>`<span class="choice">${escape(x)}</span>`).join('')}</div>` : '';
  }
  function targets(field) {
    return field.targets ? `<div class="relations">${field.targets.map(x=>x==='page'?'<span class="tag">Pages WordPress</span>':link(x)).join('')}</div>` : '';
  }
  function subfields(owner, field, parent) {
    if (!field.children) return '';
    return `<details open><summary>${field.children.length} sous-champs par ligne</summary><ul>${field.children.map(child=>`<li class="subfield"><strong>${escape(child.label)}</strong> · ${escape(child.type)} · ${child.required?'Obligatoire':'Facultatif'}<small><code>${escape(keyFor(owner,child,parent))}</code></small><small>${escape(child.note)}</small>${choices(child)}${targets(child)}${subfields(owner,child,keyFor(owner,child,parent))}</li>`).join('')}</ul></details>`;
  }
  function fieldsTable(owner) {
    return `<div class="field-heading"><h2>Groupe de champs</h2><p>${owner.fields.length} champs principaux · ${fieldCount(owner.fields)} avec les sous-champs</p></div><div class="meta"><span class="tag"><code>${escape(groupKey(owner))}</code></span><span class="tag">${owner.id==='commun'?'Localisation : les 11 types de contenus':isOption(owner)?'Localisation : page d’options correspondante':'Localisation : type de contenu = '+owner.id}</span></div><p class="technical-legend">Les identifiants proposés sont stables. « Obligatoire » désigne une règle de saisie à configurer dans ACF.</p><table class="field-table"><thead><tr><th scope="col">Champ et identifiant</th><th scope="col">Type ACF</th><th scope="col">Saisie</th><th scope="col">Détail et fonctionnement</th></tr></thead><tbody>${owner.fields.map(field=>`<tr><td><span class="field-name">${escape(field.label)}</span><code class="field-key">${escape(keyFor(owner,field))}</code></td><td><span class="type-pill">${escape(field.type)}</span></td><td><span class="${field.required || field.requiredWhen?'required':'optional'}">${field.requiredWhen?'Conditionnel':field.required?'Obligatoire':'Facultatif'}</span></td><td>${field.requiredWhen?`<strong>${escape(field.requiredWhen)}.</strong> `:''}${escape(field.note || 'Saisie libre adaptée à la fiche.')}${choices(field)}${targets(field)}${subfields(owner,field,keyFor(owner,field))}</td></tr>`).join('')}</tbody></table>`;
  }
  function tile(item,index) {
    return `<a class="tile" href="#${item.id}"><div class="tile-top"><span>${escape(item.family || 'Options globales')}</span><span>${String(index+1).padStart(2,'0')}</span></div><h3>${escape(item.title)}</h3><p>${escape(item.description)}</p><div class="tile-bottom"><span>${fieldCount(item.fields)} champs et sous-champs · 1 groupe ACF</span><span class="chevron" aria-hidden="true">↗</span></div></a>`;
  }
  function taxonomyTile(item) {
    const parents=schema.contents.filter(content=>content.taxonomy.includes(item.id));
    const detail=item.id==='ck_zone'?'Utilisée pour localiser les projets, documents, marchés, alertes et événements par village ou zone.':'Classement partagé par les contenus concernés.';
    return `<a class="tile" href="#${item.id}"><div class="tile-top"><span>Taxonomie ACF</span><span>Territoire</span></div><h3>${escape(item.title)}</h3><p>${escape(detail)}</p><div class="tile-bottom"><span>${parents.length} types de contenus liés · ${item.id==='ck_zone'?'19 villages intégrés':'catégories à valider'}</span><span class="chevron" aria-hidden="true">↗</span></div></a>`;
  }
  function overview() {
    return header('Périmètre institutionnel','La structure du portail de la commune.','Un plan détaillé pour organiser les contenus dans ACF PRO : les fiches, leurs champs, les classements et les réglages communs au site.') +
      `<div class="stats"><div class="stat"><strong>11</strong><span>types de contenus</span></div><div class="stat"><strong>${groupCount}</strong><span>groupes de champs</span></div><div class="stat"><strong>5</strong><span>taxonomies</span></div><div class="stat"><strong>3</strong><span>pages d’options</span></div></div><div class="section-head"><h2>Les contenus à gérer</h2><span class="quiet">Choisissez une fiche pour voir ses champs</span></div><div class="grid">${schema.contents.map(tile).join('')}</div><h2>Les villages dans le portail institutionnel</h2><p>Les villages ne sont pas des pages de destination ni des fiches touristiques dans ce périmètre. Ils servent à localiser les informations de la mairie, afin qu’un habitant puisse par exemple filtrer les projets, alertes ou documents qui concernent son village.</p><div class="grid">${taxonomyTile(schema.taxonomies.find(item=>item.id==='ck_zone'))}</div><div class="summary-strip"><p><strong>Les 19 villages sont intégrés comme termes ACF.</strong> La liste reprend l’orthographe publiée par <a class="inline-link" href="https://communekafountine.sn/village/" target="_blank" rel="noopener">la Commune de Kafountine</a>. Elle pourra être corrigée dans WordPress si la mairie publie un référentiel administratif différent.</p></div><h2>Une organisation commune</h2><div class="option-list"><article class="option-card"><h3>Source et vérification</h3><p>Quatre champs partagés pour tracer l’origine des informations et leur date de contrôle. Ils complètent les 11 groupes de contenus.</p><a href="#commun">Voir le groupe transversal →</a></article><article class="option-card"><h3>Des informations réutilisables</h3><p>Un élu, un service ou un document est créé une seule fois. Les autres fiches le sélectionnent grâce à une relation.</p><a href="#relations">Parcourir les relations →</a></article></div><h2>Ce qui reste dans WordPress</h2><p>Les actualités utilisent les <strong>Articles</strong>. Les pages Accueil, Mairie, Conseil, Services, Projets, Transparence et Contact présentent les contenus. Les fichiers et portraits sont conservés dans la médiathèque.</p><div class="summary-strip"><p><strong>Une proposition, avant import.</strong> ${totalFields} champs et sous-champs sont décrits dans ce plan. Leur contenu sera renseigné à partir des sources validées par la mairie.</p><p>Le périmètre couvre l’information institutionnelle. Les comptes citoyens, paiements, dossiers en ligne, signalements et consultations participatives sont exclus.</p></div><p class="source">Sources : Partie I du cahier des charges Biplateforme Kafountine ; documents du conseil communal, du personnel et répertoire municipal du dossier docs. Les choix de structure et les règles de saisie sont des propositions de conception.</p>`;
  }
  function detail(item) {
    const index = schema.contents.findIndex(x=>x.id===item.id);
    let output = header(item.family || (isOption(item)?'Page d’options':'Groupe transversal'),item.title,item.description);
    if (item.native) output += `<div class="native"><strong>Champs natifs WordPress</strong>${escape(item.native)}<br><span class="quiet">L’extrait, les révisions et l’image à la une sont disponibles. Les commentaires sont désactivés.</span></div><div class="meta"><span class="tag">Type : <code>${item.id}</code></span><span class="tag">Adresse proposée : /${item.slug}/</span><span class="tag">Brouillon puis publication</span></div>`;
    if (isOption(item)) output += `<div class="native"><strong>Options globales</strong>Menu proposé : Réglages du portail → ${escape(item.title)}. Accès administrateur, stockage global distinct pour chaque page. Aucune URL publique propre.</div><div class="meta"><span class="tag">Slug : <code>${item.id}</code></span><span class="tag">Préfixe des champs : <code>${item.id}_</code></span></div>`;
    if (item.taxonomy?.length) output += `<h2>Classements associés</h2><div class="relations">${item.taxonomy.map(x=>link(x)).join('')}</div>`;
    output += fieldsTable(item);
    if (item.id!=='commun'&&!isOption(item)) output += `<p class="common-note">Ce groupe est complété par <a href="#commun">Source et vérification</a> : source, lien, date de vérification et prochaine révision.</p>`;
    if (item.rules) output += `<h2>Règles de gestion</h2><ul class="rules">${item.rules.map(x=>`<li>${escape(x)}</li>`).join('')}</ul>`;
    const next=schema.contents[index+1];
    output += `<div class="end-nav"><a href="#overview">← Vue d’ensemble</a>${index>=0&&next?`<a href="#${next.id}">${escape(next.title)} →</a>`:'<a href="#livraison">Livraison et validation →</a>'}</div>`;
    return output;
  }
  function taxDetails(tax) {
    const parents=schema.contents.filter(x=>x.taxonomy.includes(tax.id));
    return `<article class="option-card" id="tax-${tax.id}"><h2 style="margin-top:0">${escape(tax.title)}</h2><p>${escape(tax.description)}</p><div class="meta"><span class="tag"><code>${tax.id}</code></span><span class="tag">Hiérarchique : parents et sous-catégories</span></div><h3>Associée à</h3><div class="relations">${parents.map(x=>link(x.id)).join('')}</div><h3 style="margin-top:22px">Catégories initiales proposées</h3>${tax.terms.length?`<div class="choices">${tax.terms.map(x=>`<span class="choice">${escape(x)}</span>`).join('')}</div>`:'<p>À compléter après validation du référentiel municipal.</p>'}<p style="margin-top:17px">${escape(tax.note)}</p></article>`;
  }
  function taxonomyView(tax) {
    return header('Classement des contenus',tax?tax.title:'Les cinq taxonomies.','Des catégories partagées pour classer les fiches et préparer les filtres de recherche.')+`<div class="option-list">${(tax?[tax]:schema.taxonomies).map(taxDetails).join('')}</div><div class="summary-strip"><p>Les JSON ACF créent les définitions des taxonomies. Les termes de départ seront fournis séparément et devront être ajoutés ou importés dans WordPress.</p><p>Les statuts de projet, de session, de marché et d’événement restent des champs de sélection.</p></div>`;
  }
  function optionsView() {
    return header('Réglages globaux','Trois pages d’options.','Les informations communes à tout le portail sont modifiées à un seul endroit, puis réutilisées dans les modèles de pages.')+`<div class="grid">${schema.options.map(tile).join('')}</div><h2>Organisation dans l’administration</h2><div class="native"><strong>Menu Réglages du portail</strong>Identité de la commune<br>Coordonnées et horaires<br>Paramètres du portail</div><p class="source">Chaque page possède son groupe dédié. Les groupes de contenus ne s’affichent pas dans ces options. Les noms des champs sont préfixés pour éviter les collisions entre les trois pages.</p>`;
  }
  function relationView() {
    const rows=[];
    function walk(owner,fields,path='') {for(const field of fields){if(field.targets) for(const target of field.targets) rows.push({source:owner.id,target,label:path+field.label,note:field.note,multiple:!!field.multiple});if(field.children) walk(owner,field.children,field.label+' / ');}}
    for(const owner of [...schema.contents,...schema.options]) walk(owner,owner.fields);
    rows.push({source:'ck_service',target:'ck_service',label:'Service parent',note:'Hiérarchie native WordPress.',multiple:false});
    return header('Liens entre les fiches','Une information, une source.','Les relations sélectionnent des fiches existantes. Elles évitent de ressaisir les noms, les coordonnées et les documents dans plusieurs rubriques.')+`<div class="summary-strip"><p><strong>Trois relations structurantes :</strong> commission → élus et rôles ; démarche → service compétent ; document → session ou projet.</p><p>Les listes inverses (commissions d’un élu, délibérations d’une session, documents d’un projet) seront produites par les modèles à partir de ces liens.</p></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Depuis</th><th>Champ</th><th>Vers</th><th>Sélection</th></tr></thead><tbody>${rows.map(row=>`<tr><td>${link(row.source)}</td><td>${escape(row.label)}</td><td>${row.target==='page'?'Page WordPress':link(row.target)}</td><td>${row.multiple?'Plusieurs fiches':'Une fiche'}${row.label.includes('Composition')?' par ligne':''}</td></tr>`).join('')}</tbody></table></div>`;
  }
  function delivery() {
    const files=[['01-types-contenus.json','11 définitions de types de contenus.'],['02-taxonomies.json','5 définitions de taxonomies et leurs associations.'],['03-pages-options.json','3 pages d’options globales.'],['04-groupes-champs.json','15 groupes de champs, avec leurs règles de localisation.'],['05-import-complet.json','Les quatre ensembles précédents dans un fichier ACF unique.'],['06-termes-taxonomies.json','Les termes proposés, dans un format distinct de l’import ACF standard.'],['README.md','Procédure d’import, correspondances, vérifications et limites.']];
    return header('Mise en œuvre','Valider, puis générer les JSON.','Cette page est le document de conception. Les exports importables seront produits après validation de la structure.')+`<div class="file-list">${files.map(([name,note])=>`<div class="file-row"><strong><code>${name}</code></strong><p>${note}</p></div>`).join('')}</div><h2>Ordre de mise en place</h2><div class="step"><b>01</b><p><strong>Vérifier ACF PRO et sauvegarder</strong>Vérifier que la version installée prend en charge les types de contenus, taxonomies et pages d’options dans les imports JSON.</p></div><div class="step"><b>02</b><p><strong>Importer la structure validée</strong>Utiliser le fichier complet ou les fichiers séparés. Les clés stables doivent permettre les mises à jour sans recréer les mêmes objets.</p></div><div class="step"><b>03</b><p><strong>Ajouter les catégories et renseigner les contenus</strong>Confirmer les villages et les libellés métiers, puis saisir les services, agents, élus et fiches liées. Les liens facultatifs permettent une saisie progressive.</p></div><div class="step"><b>04</b><p><strong>Brancher les modèles du site</strong>Configurer les fiches et listes Elementor, les filtres, les options globales et les affichages dérivés.</p></div><h2>Règles de saisie à appliquer</h2><ul class="validation-list"><li>Les choix de sélection et les champs obligatoires décrits dans ce plan seront configurés dans ACF.</li><li>Les montants sont positifs ou nuls, exprimés en FCFA. Le montant d’une démarche est obligatoire uniquement pour un coût fixe.</li><li>Les dates de fin doivent suivre les dates de début. Les responsables hiérarchiques ne doivent pas créer de boucle.</li><li>La part communale ne peut pas dépasser le budget total lorsqu’ils sont tous les deux renseignés.</li><li>Les contrôles croisés entre champs, les boucles et certains contrôles de doublons nécessiteront une validation PHP complémentaire ; un JSON seul ne les exécute pas.</li><li>L’image et les fichiers retournent un identifiant de média ; les relations retournent les identifiants des fiches.</li></ul><h2>Ce que les JSON ne réalisent pas</h2><p>Ils ne créent ni modèles Elementor, ni formulaires citoyens, ni bandeau automatique, ni expiration des alertes, ni rappels de révision. Les sélections et dates sont prévues pour permettre cette intégration ensuite.</p><h2>Points à confirmer</h2><ul class="validation-list"><li>Les 11 types, leurs champs obligatoires et les 3 pages d’options.</li><li>Les familles de démarches, types de documents et thématiques proposées.</li><li>La liste officielle des villages et zones, encore à fournir ou à confirmer.</li><li>Les informations professionnelles et documents destinés à être publiés.</li></ul><p class="source">Document préparé le ${escape(schema.date)} · ${escape(schema.status)} · Aucun contenu importé ni publié sur communekafountine.com.</p>`;
  }
  function navigation() {
    const sections=[['Vue d’ensemble',[['overview','Vue d’ensemble','11']]],...['Gouvernance','Institution','Services et information','Action publique','Transparence','Communication'].map(family=>[family,schema.contents.filter(x=>x.family===family).map(x=>[x.id,x.title,String(x.fields.length)])]),['Structure partagée',[['commun','Source et vérification','4'],['taxonomies','Taxonomies','5'],['options','Pages d’options','3'],['relations','Relations',''],['livraison','Livraison et validation','']]]];
    nav.innerHTML=sections.map(([name,entries])=>`<div class="nav-group">${name}</div>${entries.map(([id,label,count])=>`<a class="nav-link" href="#${id}" data-route="${id}"><span>${escape(label)}</span><span class="nav-count">${count}</span></a>`).join('')}`).join('');
  }
  function render() {
    let id=location.hash.slice(1)||'overview';
    if(!knownRoutes.has(id)) id='overview';
    search.value='';
    if(id==='overview') main.innerHTML=overview();
    else if(id==='taxonomies') main.innerHTML=taxonomyView();
    else if(id==='options') main.innerHTML=optionsView();
    else if(id==='relations') main.innerHTML=relationView();
    else if(id==='livraison') main.innerHTML=delivery();
    else if(schema.taxonomies.some(x=>x.id===id)) main.innerHTML=taxonomyView(find(id));
    else main.innerHTML=detail(find(id));
    for(const anchor of nav.querySelectorAll('[data-route]')){const active=anchor.dataset.route===id || (isOption(find(id)||{})&&anchor.dataset.route==='options') || (id.startsWith('ck_')&&schema.taxonomies.some(x=>x.id===id)&&anchor.dataset.route==='taxonomies');if(active) anchor.setAttribute('aria-current','page');else anchor.removeAttribute('aria-current');}
    document.title=(find(id)?.title || ({overview:'Vue d’ensemble',taxonomies:'Taxonomies',options:'Pages d’options',relations:'Relations',livraison:'Livraison et validation'})[id])+' — ACF · Kafountine';
  }
  search.addEventListener('input',()=>{
    const query=normalize(search.value.trim());if(!query){render();search.focus();return;}
    const matches=all.map(item=>({item,fields:flatten(item.fields).filter(x=>normalize([x.label,x.name,x.type,x.note,...(x.choices||[])].join(' ')).includes(query))})).filter(x=>normalize(x.item.title+' '+x.item.description).includes(query)||x.fields.length);
    const taxMatches=schema.taxonomies.filter(x=>normalize(x.title+' '+x.description+' '+x.terms.join(' ')).includes(query));
    main.innerHTML=header('Recherche dans le plan','Résultats de recherche','Retrouvez un groupe, un type de contenu ou un champ de la proposition.')+`<p class="count-result" role="status">${matches.length+taxMatches.length} rubriques correspondent à « ${escape(search.value)} ».</p>${matches.map(({item,fields})=>`<a class="search-result" href="#${item.id}"><strong>${escape(item.title)}</strong><span>${escape(fields.length?fields.map(x=>x.label).join(' · '):item.description)}</span></a>`).join('')}${taxMatches.map(item=>`<a class="search-result" href="#${item.id}"><strong>${escape(item.title)}</strong><span>Taxonomie · ${escape(item.terms.join(' · ')||'Liste des zones à confirmer')}</span></a>`).join('')}${!matches.length&&!taxMatches.length?'<p class="empty">Aucun résultat. Essayez « mandat », « document » ou « téléphone ».</p>':''}`;
  });
  function preparePrint(){const printable=document.getElementById('print-content');printable.innerHTML=`<section class="print-section">${overview()}</section>`+all.map(item=>`<section class="print-section">${detail(item)}</section>`).join('')+`<section class="print-section">${taxonomyView()}</section><section class="print-section">${relationView()}</section><section class="print-section">${delivery()}</section>`;}
  document.getElementById('print').addEventListener('click',()=>{preparePrint();window.print();});
  window.addEventListener('beforeprint',preparePrint);
  document.addEventListener('click',event=>{const anchor=event.target.closest('a[href^="#"]');if(!anchor)return;if(anchor.hash==='#main'){event.preventDefault();main.focus();return;}if(anchor.hash===location.hash){event.preventDefault();render();window.scrollTo({top:0,behavior:'instant'});main.focus({preventScroll:true});}});
  window.addEventListener('hashchange',()=>{render();window.scrollTo({top:0,behavior:'instant'});main.focus({preventScroll:true});});
  navigation();render();
})();
