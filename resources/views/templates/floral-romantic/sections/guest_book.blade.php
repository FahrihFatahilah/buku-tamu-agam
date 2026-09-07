@php $gbSection=$sections->firstWhere('section_key','guest_book'); $entries=\App\Models\GuestBookEntry::where('wedding_id',$wedding->id)->approved()->latest()->limit(20)->get(); $invId=$wedding->short_id??$wedding->public_id; $gbUrl=url("/{$invId}/{$wedding->slug}/guestbook").($guest?'?t='.$guest->invitation_token:''); @endphp
<section id="guest_book" class="py-20 px-6 bg-[#f9f0f0]" x-data="guestBook('{{ $gbUrl }}','{{ csrf_token() }}')">
    <div class="max-w-xl mx-auto">
        <p class="text-[#b5606a]/60 text-xs tracking-[0.4em] uppercase mb-3 text-center reveal">Ucapan & Doa</p>
        <h2 class="font-display text-2xl text-[#2a1a1a] italic text-center mb-10 reveal">Buku Tamu</h2>
        <div x-show="sent" x-transition class="mb-6 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm text-center">Ucapan berhasil dikirim!</div>
        <div x-show="error" x-transition class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-600 text-sm text-center"><span x-text="error"></span></div>
        <form @submit.prevent="submit" class="space-y-4 mb-12 reveal" x-show="!sent">
            <input type="text" x-model="form.name" placeholder="Nama Anda" value="{{ $guest?->name }}" {{ $guest?'readonly':'' }} class="w-full px-4 py-3 border border-[#b5606a]/20 bg-white text-sm focus:outline-none focus:border-[#b5606a]/50 transition-colors">
            <textarea x-model="form.message" rows="3" placeholder="Tulis ucapan dan doa..." class="w-full px-4 py-3 border border-[#b5606a]/20 bg-white text-sm focus:outline-none focus:border-[#b5606a]/50 transition-colors resize-none"></textarea>
            <button type="submit" :disabled="loading" class="w-full py-3 bg-[#b5606a] text-white text-xs tracking-[0.2em] uppercase hover:bg-[#9a4f58] transition-colors disabled:opacity-50"><span x-show="!loading">Kirim Ucapan</span><span x-show="loading">Mengirim...</span></button>
        </form>
        <div class="space-y-4">
            @foreach($entries as $entry)
            <div class="border-b border-[#b5606a]/10 pb-4 last:border-0">
                <div class="flex items-baseline justify-between gap-2 mb-1">
                    <p class="font-display text-sm text-[#2a1a1a] italic">{{ $entry->name }}</p>
                    <time class="text-[#2a1a1a]/30 text-xs shrink-0">{{ $entry->created_at->diffForHumans() }}</time>
                </div>
                <p class="text-[#2a1a1a]/50 text-sm leading-relaxed">{{ $entry->message }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
<script>
function guestBook(url,token){return{url,token,form:{name:'{{ $guest?->name ?? '' }}',message:''},loading:false,sent:false,error:null,errors:{},async submit(){this.loading=true;this.error=null;this.errors={};try{const res=await fetch(this.url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':this.token,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify(this.form)});const data=await res.json();if(res.status===422){this.errors=data.errors??{};}else if(data.success){this.sent=true;}else{this.error=data.message??'Terjadi kesalahan.';}}catch(e){this.error='Gagal mengirim.';}finally{this.loading=false;}}};}
</script>
