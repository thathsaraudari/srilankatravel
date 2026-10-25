document.addEventListener('DOMContentLoaded',()=>{
  const toggle=document.querySelector('.nav-toggle'),nav=document.querySelector('.primary-nav');
  if(toggle&&nav)toggle.addEventListener('click',()=>{const open=toggle.getAttribute('aria-expanded')==='true';toggle.setAttribute('aria-expanded',String(!open));nav.classList.toggle('is-open',!open);});
  document.querySelectorAll('[data-slt-language]').forEach(select=>select.addEventListener('change',()=>{if(select.value)window.location.href=select.value;}));
  const strings=(window.SLT_I18N&&SLT_I18N.strings)||{},lang=(window.SLT_I18N&&SLT_I18N.lang)||'fr';
  const locale={fr:'fr-FR',en:'en-GB',de:'de-DE',nl:'nl-NL',es:'es-ES'}[lang]||'fr-FR';
  document.querySelectorAll('.slt-priced-booking').forEach(form=>{
    const num=(name,fallback=0)=>Number(form.dataset[name]||fallback),currency=form.dataset.currency||'EUR',money=new Intl.NumberFormat(locale,{style:'currency',currency,maximumFractionDigits:0});
    const rateFor=party=>party<=1?num('price1'):party===2?num('price2'):party<=4?num('price3-4'):party<=6?num('price5-6'):num('price7Plus');
    const calculate=()=>{
      const totalEl=form.querySelector('[data-slt-total]'),depositEl=form.querySelector('[data-slt-deposit]'),noteEl=form.querySelector('[data-slt-rate-note]'),seasonEl=form.querySelector('[data-slt-season]');
      if(!totalEl||!depositEl||!noteEl)return;
      if((form.dataset.pricingMode||'request')!=='matrix'){totalEl.textContent=strings.confirm||'Tarif à confirmer';depositEl.textContent='—';noteEl.textContent=strings.final||'Le prix final sera confirmé avant réservation.';if(seasonEl)seasonEl.hidden=true;return;}
      const adults=Math.max(1,Number(form.elements.adults?.value||1)),children=Math.max(0,Number(form.elements.children?.value||0)),singles=Math.max(0,Number(form.elements.single_rooms?.value||0)),party=adults+children,adultRate=rateFor(party);
      if(!adultRate){totalEl.textContent=strings.confirm||'Tarif à confirmer';depositEl.textContent='—';noteEl.textContent=strings.no_rate||'Aucun tarif n’est configuré pour cette taille de groupe.';if(seasonEl)seasonEl.hidden=true;return;}
      const childDiscount=Math.min(100,Math.max(0,num('childDiscount'))),childRate=adultRate*(1-childDiscount/100),singleSupplement=Math.max(0,num('singleSupplement'));
      let total=(adultRate*adults)+(childRate*children)+(singleSupplement*singles);
      const date=form.elements.travel_date?.value||'',start=form.dataset.seasonStart||'',end=form.dataset.seasonEnd||'',surcharge=Math.max(0,num('seasonSurcharge')),seasonal=Boolean(date&&start&&end&&date>=start&&date<=end&&surcharge>0);
      if(seasonal)total+=total*(surcharge/100);
      const depositPercent=Math.min(100,Math.max(0,num('depositPercent',30))),deposit=total*(depositPercent/100);
      totalEl.textContent=money.format(total);depositEl.textContent=money.format(deposit);
      let note=money.format(adultRate)+' / '+(strings.adult||'adulte');
      if(children>0)note+=' · '+money.format(childRate)+' / '+(strings.child||'enfant');
      if(singles>0&&singleSupplement>0)note+=' · '+money.format(singleSupplement)+' / '+(strings.single||'chambre individuelle');
      noteEl.textContent=note;
      if(seasonEl){seasonEl.hidden=!seasonal;if(seasonal)seasonEl.textContent=(strings.season||'Supplément haute saison de %s%% inclus.').replace('%s',String(surcharge));}
    };
    ['adults','children','single_rooms','travel_date'].forEach(name=>{const el=form.elements[name];if(el){el.addEventListener('input',calculate);el.addEventListener('change',calculate);}});
    calculate();
  });
});
document.addEventListener('change',e=>{if(e.target.matches('.slt-booking-demo-form input[name="preferred_payment_method"]')){const form=e.target.closest('.slt-booking-demo-form'),hidden=form&&form.querySelector('.slt-booking-message');if(hidden)hidden.value='Booking demo — preferred payment method: '+e.target.value;}});
