param(
    [string]$SiteUrl = 'https://communekafountine.com'
)

$ErrorActionPreference = 'Stop'
$photoDirectory = Join-Path $PSScriptRoot '..\docs\1 - SUR LE CONSEIL COMMUNAL-20261004T110742Z-1-001\1 - SUR LE CONSEIL COMMUNAL\Photo Conseillers'
$configPath = Join-Path $env:USERPROFILE '.codex\config.toml'
$config = Get-Content -Raw -LiteralPath $configPath
$authorization = [regex]::Match($config, '(?ms)\[mcp_servers\.commune-de-kafountine-elementor\.(?:headers|http_headers)\].*?Authorization\s*=\s*"Basic\s+([^\"]+)"').Groups[1].Value
if (-not $authorization) { throw 'En-tête MCP Commune de Kafountine introuvable dans ~/.codex/config.toml.' }

# Les deux noms dont l'orthographe diffère du registre sont explicitement rapprochés ici.
$photos = @(
    @{ File='Abdoulaye DEMBA Commission finance.jpeg'; Slug='abdoulaye-demba'; Role='Commission finance' },
    @{ File='Adama Karim KEITA Commission planification et finance.jpeg'; Slug='adama-karim-keita'; Role='Commission planification et finance' },
    @{ File='Aïssata SANÉ Commission santé.jpeg'; Slug='aissata-sane'; Role='Commission santé' },
    @{ File='Aissatou DIASSY Commission santé.jpeg'; Slug='aissatou-diassy'; Role='Commission santé' },
    @{ File='Aliou Frédéric SAMBOU 4e adjoint au maire.jpeg'; Slug='aliou-frederic-sambou'; Role='4e adjoint au maire' },
    @{ File='Angélique SINON Commission santé.jpeg'; Slug='angelique-sina'; Role='Commission santé' },
    @{ File='Awa MBALLO Commission santé.jpeg'; Slug='awa-mballo'; Role='Commission santé' },
    @{ File='Binta BIAYE Conseillère municipale.jpeg'; Slug='binta-biaye'; Role='Conseillère municipale' },
    @{ File='Boubacar SAGNA Commission tourisme.jpeg'; Slug='boubacar-sagna'; Role='Commission tourisme' },
    @{ File='Bourama DIATTA Commission domaniale.jpeg'; Slug='bourama-diatta'; Role='Commission domaniale' },
    @{ File='Clarisse DIATTA Commission éducation.jpeg'; Slug='clarice-diatta'; Role='Commission éducation' },
    @{ File='Diarietou GOUDIABY Commission éducation.jpeg'; Slug='diarietou-goudiaby'; Role='Commission éducation' },
    @{ File='Dienaba SAMBOU 1re adjointe au maire.jpeg'; Slug='dieynaba-sambou'; Role='1re adjointe au maire' },
    @{ File='Diouma KONTÉ Conseillère municipale.jpeg'; Slug='diouma-konte'; Role='Conseillère municipale' },
    @{ File='Djibril Dianna DIASSY Commission domaniale.jpeg'; Slug='djibril-diassy'; Role='Commission domaniale' },
    @{ File='Gnara DIABANG 3e adjointe au maire.jpeg'; Slug='gnara-diabang'; Role='3e adjointe au maire' },
    @{ File='Gnima DIATTA Commission environnement.jpeg'; Slug='gnima-diatta'; Role='Commission environnement' },
    @{ File='Mariama SAGNA Commission environnement.jpeg'; Slug='mariama-sagna'; Role='Commission environnement' },
    @{ File='Ousseynou MBAYE 2e adjoint au maire.jpeg'; Slug='ouseynou-mbaye'; Role='2e adjoint au maire' },
    @{ File='Paul DIATTA Commission environnement.jpeg'; Slug='paul-diatta'; Role='Commission environnement' },
    @{ File='Saliou SAMBOU Commission partenariat décentralisé.jpeg'; Slug='saliou-sambou'; Role='Commission partenariat décentralisé' },
    @{ File='Seny DIABANG Commission jeunesse, sports et loisirs.jpeg'; Slug='seny-diabang'; Role='Commission jeunesse, sports et loisirs' },
    @{ File='Serigne Moustapha GOUDIABY Commission éducation.jpeg'; Slug='serigne-moustapha-goudiaby'; Role='Commission éducation' },
    @{ File='Sitapha DIASSY Commission culture.jpeg'; Slug='sitapha-diassy'; Role='Commission culture' },
    @{ File='Souleymane CAMARA Commission action sociale.jpeg'; Slug='souleymane-camara'; Role='Commission action sociale' },
    @{ File='Tombong SONKO Commission éducation.jpeg'; Slug='tombong-sonko'; Role='Commission éducation' }
)

$headers = @{ Authorization = "Basic $authorization" }
$posts = Invoke-RestMethod -Uri "$SiteUrl/wp-json/wp/v2/ck_elu?per_page=100&context=edit" -Headers $headers
$bySlug = @{}
foreach ($post in $posts) { $bySlug[$post.slug] = $post }

$result = foreach ($photo in $photos) {
    if (-not $bySlug.ContainsKey($photo.Slug)) { throw "Fiche élu introuvable pour $($photo.File) ($($photo.Slug))." }
    $post = $bySlug[$photo.Slug]
    if ([int]$post.featured_media -gt 0) {
        [pscustomobject]@{ Elu=[System.Net.WebUtility]::HtmlDecode($post.title.rendered); Fichier=$photo.File; MediaId=$post.featured_media; PostId=$post.id; Statut='déjà associé' }
        continue
    }
    $path = Join-Path $photoDirectory $photo.File
    if (-not (Test-Path -LiteralPath $path)) { throw "Fichier photo introuvable : $path" }
    $title = [System.Net.WebUtility]::HtmlDecode($post.title.rendered)
    $altText = "Portrait de $title, $($photo.Role), Conseil municipal de Kafountine"
    $uploadHeaders = @{ Authorization = "Basic $authorization"; 'Content-Disposition' = ('attachment; filename="portrait-' + $photo.Slug + '.jpeg"'); 'Content-Type' = 'image/jpeg' }
    $media = Invoke-RestMethod -Uri "$SiteUrl/wp-json/wp/v2/media" -Method Post -Headers $uploadHeaders -InFile $path
    $mediaUpdate = @{ alt_text = $altText; caption = $altText; post = [int]$post.id } | ConvertTo-Json -Compress
    Invoke-RestMethod -Uri "$SiteUrl/wp-json/wp/v2/media/$($media.id)" -Method Post -Headers ($headers + @{ 'Content-Type'='application/json' }) -Body $mediaUpdate | Out-Null
    $postUpdate = @{ featured_media = [int]$media.id } | ConvertTo-Json -Compress
    Invoke-RestMethod -Uri "$SiteUrl/wp-json/wp/v2/ck_elu/$($post.id)" -Method Post -Headers ($headers + @{ 'Content-Type'='application/json' }) -Body $postUpdate | Out-Null
    [pscustomobject]@{ Elu=$title; Fichier=$photo.File; MediaId=$media.id; PostId=$post.id; Statut='importé et associé' }
}

$result | Format-Table -AutoSize
