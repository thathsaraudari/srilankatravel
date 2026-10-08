document.addEventListener('DOMContentLoaded',()=>{
  const toggle=document.querySelector('.nav-toggle');
  const nav=document.querySelector('.primary-nav');
  if(toggle&&nav){
    toggle.addEventListener('click',()=>{
      const open=toggle.getAttribute('aria-expanded')==='true';
      toggle.setAttribute('aria-expanded',String(!open));
      nav.classList.toggle('is-open',!open);
    });
  }

  document.querySelectorAll('.slt-priced-booking').forEach(form=>{
    const num=(name,fallback=0)=>Number(form.dataset[name]||fallback);
    const currency=form.dataset.currency||'EUR';
    const money=new Intl.NumberFormat('fr-FR',{style:'currency',currency,maximumFractionDigits:0});

    const rateFor=party=>{
      if(party<=1)return num('price1');
      if(party===2)return num('price2');
      if(party<=4)return num('price3-4');
      if(party<=6)return num('price5-6');
      return num('price7Plus');
    };

    const calculate=()=>{
      const totalEl=form.querySelector('[data-slt-total]');
      const depositEl=form.querySelector('[data-slt-deposit]');
      const noteEl=form.querySelector('[data-slt-rate-note]');
      const seasonEl=form.querySelector('[data-slt-season]');
      if(!totalEl||!depositEl||!noteEl)return;

      if((form.dataset.pricingMode||'request')!=='matrix'){
        totalEl.textContent='Tarif à confirmer';
        depositEl.textContent='—';
        noteEl.textContent='Le prix final sera confirmé avant réservation.';
        if(seasonEl)seasonEl.hidden=true;
        return;
      }

      const adults=Math.max(1,Number(form.elements.adults?.value||1));
      const children=Math.max(0,Number(form.elements.children?.value||0));
      const singles=Math.max(0,Number(form.elements.single_rooms?.value||0));
      const party=adults+children;
      const adultRate=rateFor(party);

      if(!adultRate){
        totalEl.textContent='Tarif à confirmer';
        depositEl.textContent='—';
        noteEl.textContent='Aucun tarif n’est configuré pour cette taille de groupe.';
        if(seasonEl)seasonEl.hidden=true;
        return;
      }

      const childDiscount=Math.min(100,Math.max(0,num('childDiscount')));
      const childRate=adultRate*(1-childDiscount/100);
      const singleSupplement=Math.max(0,num('singleSupplement'));
      let total=(adultRate*adults)+(childRate*children)+(singleSupplement*singles);

      const date=form.elements.travel_date?.value||'';
      const start=form.dataset.seasonStart||'';
      const end=form.dataset.seasonEnd||'';
      const surcharge=Math.max(0,num('seasonSurcharge'));
      const seasonal=Boolean(date&&start&&end&&date>=start&&date<=end&&surcharge>0);
      if(seasonal)total+=total*(surcharge/100);

      const depositPercent=Math.min(100,Math.max(0,num('depositPercent',30)));
      const deposit=total*(depositPercent/100);

      totalEl.textContent=money.format(total);
      depositEl.textContent=money.format(deposit);
      let note=money.format(adultRate)+' / adulte';
      if(children>0)note+=' · '+money.format(childRate)+' / enfant';
      if(singles>0&&singleSupplement>0)note+=' · '+money.format(singleSupplement)+' / chambre individuelle';
      noteEl.textContent=note;
      if(seasonEl){
        seasonEl.hidden=!seasonal;
        if(seasonal)seasonEl.textContent='Supplément haute saison de '+surcharge+' % inclus.';
      }
    };

    ['adults','children','single_rooms','travel_date'].forEach(name=>{
      const el=form.elements[name];
      if(el){el.addEventListener('input',calculate);el.addEventListener('change',calculate);}
    });
    calculate();
  });
});

document.addEventListener('change',e=>{
  if(e.target.matches('.slt-booking-demo-form input[name="preferred_payment_method"]')){
    const form=e.target.closest('.slt-booking-demo-form');
    const hidden=form&&form.querySelector('.slt-booking-message');
    if(hidden)hidden.value='Réservation démo — moyen de paiement souhaité : '+e.target.value;
  }
});
