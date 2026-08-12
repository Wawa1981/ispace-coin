/**
 * Chaînes TV / YouTube FINANCE & marchés (popup + footer).
 * Uniquement finance / business / crypto — PAS d’infos générales
 * (pas France 24, Sky, DW, CNA, Euronews, Al Jazeera).
 */
export const financeChannels = [
  {
    title: 'BFM Business',
    source: 'BFM Business',
    // Direct officiel (pas YouTube) — le site a un live 24/7
    sourceUrl: 'https://www.bfmtv.com/economie/en-direct/',
    channelUrl: 'https://www.youtube.com/@BFMBusiness',
    channelId: 'UCUsBMOIUl_ad6JUOC16DpmQ',
    // Flux HLS officiel BFM Business TV
    hlsUrl: 'https://live-cdn-stream-euw1.bfmb.bct.nextradiotv.com/master.m3u8',
    logo: 'https://unavatar.io/youtube/@BFMBusiness?fallback=https://www.google.com/s2/favicons?domain=bfmtv.com&sz=64',
  },
  {
    title: 'Bloomberg Television',
    source: 'Bloomberg',
    sourceUrl: 'https://www.bloomberg.com/live',
    channelUrl: 'https://www.youtube.com/@markets',
    channelId: 'UCIALMKvObZNtJ6AmdCLP7Lg',
    logo: 'https://unavatar.io/youtube/@markets?fallback=https://www.google.com/s2/favicons?domain=bloomberg.com&sz=64',
  },
  {
    title: 'CNBC',
    source: 'CNBC',
    // Site cnbc.com/live-tv = paywall câble (pas de HLS public comme BFM)
    // Live gratuit = YouTube CNBC (souvent marathon 24/7)
    sourceUrl: 'https://www.cnbc.com/live-tv/',
    channelUrl: 'https://www.youtube.com/@CNBC',
    // Canal principal CNBC (live 24/7 fréquent) — pas @CNBCtelevision (souvent hors live)
    channelId: 'UCvJJ_dzjViJCoLf5uKUTwoA',
    // Fallback si le principal n’est pas en live
    altChannelIds: ['UCrp_UI8XtuYfpiqluWLD7Lw'],
    logo: 'https://unavatar.io/youtube/@CNBC?fallback=https://www.google.com/s2/favicons?domain=cnbc.com&sz=64',
  },
  {
    title: 'Yahoo Finance',
    source: 'Yahoo Finance',
    sourceUrl: 'https://finance.yahoo.com/',
    channelUrl: 'https://www.youtube.com/@YahooFinance',
    channelId: 'UCEAZeUIeJs0IjQiqTCdVSIg',
    logo: 'https://unavatar.io/youtube/@YahooFinance?fallback=https://www.google.com/s2/favicons?domain=finance.yahoo.com&sz=64',
  },
  {
    title: 'Fox Business',
    source: 'Fox Business',
    sourceUrl: 'https://www.foxbusiness.com/video/5640669329001',
    channelUrl: 'https://www.youtube.com/@FoxBusiness',
    channelId: 'UCCXoCcu9Rp7NPbTzIvogpZg',
    // Pas de Live (flux protégé / peu fiable) — dernières vidéos uniquement
    liveDisabled: true,
    logo: 'https://unavatar.io/youtube/@FoxBusiness?fallback=https://www.google.com/s2/favicons?domain=foxbusiness.com&sz=64',
  },
  {
    title: 'BNN Bloomberg',
    source: 'BNN Bloomberg',
    sourceUrl: 'https://www.bnnbloomberg.ca/video/live/',
    channelUrl: 'https://www.youtube.com/@BNNBloomberg',
    channelId: 'UC5aNPmKYwbudeNngDMTY3lw',
    liveDisabled: true,
    logo: 'https://unavatar.io/youtube/@BNNBloomberg?fallback=https://www.google.com/s2/favicons?domain=bnnbloomberg.ca&sz=64',
  },
  {
    title: 'Kitco NEWS',
    source: 'Kitco',
    sourceUrl: 'https://www.kitco.com/',
    channelUrl: 'https://www.youtube.com/@kitco',
    channelId: 'UC9ijza42jVR3T6b8bColgvg',
    liveDisabled: true,
    logo: 'https://unavatar.io/youtube/@kitco?fallback=https://www.google.com/s2/favicons?domain=kitco.com&sz=64',
  },
  {
    title: 'Reuters',
    source: 'Reuters',
    // Live YouTube principal (ne pas remplacer par reuters.com/markets)
    sourceUrl: 'https://www.youtube.com/@Reuters/live',
    channelUrl: 'https://www.youtube.com/@Reuters',
    channelId: 'UChqUTb7kYRX8-EiaN3XFrSQ',
    logo: 'https://unavatar.io/youtube/@Reuters?fallback=https://www.google.com/s2/favicons?domain=reuters.com&sz=64',
  },
  {
    title: 'Financial Times',
    source: 'Financial Times',
    sourceUrl: 'https://www.youtube.com/@FinancialTimes',
    channelUrl: 'https://www.youtube.com/@FinancialTimes',
    channelId: 'UCoUxsWakJucWg46KW5RsvPw',
    liveDisabled: true,
    logo: 'https://unavatar.io/youtube/@FinancialTimes?fallback=https://www.google.com/s2/favicons?domain=ft.com&sz=64',
  },
  {
    title: 'CoinDesk',
    source: 'CoinDesk',
    sourceUrl: 'https://www.coindesk.com/',
    channelUrl: 'https://www.youtube.com/@CoinDesk',
    channelId: 'UC7TghOL755nBk7HelHoi9LQ',
    liveDisabled: true,
    logo: 'https://unavatar.io/youtube/@CoinDesk?fallback=https://www.google.com/s2/favicons?domain=coindesk.com&sz=64',
  },
]
