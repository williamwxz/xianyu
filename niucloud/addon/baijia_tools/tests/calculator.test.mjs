import test from 'node:test'
import assert from 'node:assert/strict'
import { calculate, decimal, validHistory } from '../uni-app/lib/calculator.mjs'

test('8.5 折 and fractional cents round correctly', () => {
  assert.equal(calculate('discount', {price:'199',discount:'8.5'}).finalText,'169.15')
  assert.equal(calculate('discount', {price:'0.05',discount:'5'}).finalText,'0.03')
  assert.equal(calculate('discount', {price:'19.99',discount:'9.9'}).finalText,'19.79')
})
test('full price, very small discount and upper amount bound', () => {
  assert.equal(calculate('discount', {price:'999999',discount:'10'}).saved,0)
  assert.equal(calculate('discount', {price:'1',discount:'0.01'}).final,0)
  assert.equal(calculate('discount', {price:'999999',discount:'9.99'}).finalText,'998999.00')
})
test('one-time and repeat reductions, exact boundary and unmet threshold', () => {
  const f={price:'650',threshold:'200',reduction:'30',repeat:false}
  assert.equal(calculate('reduction', f).finalText,'620.00')
  assert.equal(calculate('reduction',{...f,repeat:true}).finalText,'560.00')
  assert.equal(calculate('reduction',{...f,price:'200'}).finalText,'170.00')
  assert.equal(calculate('reduction',{...f,price:'199.99'}).saved,0)
  assert.equal(calculate('reduction',{price:'0.3',threshold:'0.1',reduction:'0.01',repeat:true}).finalText,'0.27')
})
test('coupon eligibility uses rounded discounted price, with a zero floor', () => {
  const f={price:'200',discount:'8',coupon:'20',couponThreshold:'160'}
  assert.equal(calculate('coupon',f).finalText,'140.00')
  assert.equal(calculate('coupon',{...f,couponThreshold:'160.01'}).finalText,'160.00')
  assert.equal(calculate('coupon',{...f,coupon:'300'}).final,0)
  assert.equal(calculate('coupon',{...f,coupon:'0',couponThreshold:'0'}).finalText,'160.00')
})
test('invalid and incomplete amounts do not silently become zero', () => {
  for(const value of ['', ' ', '-1', '1e3', 'NaN', 'Infinity', '0.001', '12abc', '1000000', '1.']) {
    assert.throws(()=>decimal(value,'金额',999999),value)
  }
  for(const value of ['0','10.01','85']) assert.throws(()=>calculate('discount',{price:'100',discount:value}))
  assert.throws(()=>calculate('reduction',{price:'100',threshold:'0',reduction:'1'}))
  assert.throws(()=>calculate('reduction',{price:'100',threshold:'10',reduction:'11'}))
  assert.throws(()=>calculate('other',{price:'100'}))
})
test('savings always reconcile and results stay between zero and original', () => {
  for(let p=1;p<=50000;p+=137) {
    for (const mode of ['discount','reduction','coupon']) {
      const r=calculate(mode,{price:(p/100).toFixed(2),discount:'8.65',threshold:'100',reduction:'20',repeat:true,coupon:'80',couponThreshold:'10'})
      assert.equal(r.saved+r.final,r.original)
      assert.ok(Number.isSafeInteger(r.final) && r.final>=0 && r.final<=r.original)
    }
  }
})
test('history rejects malformed records, recomputes values and caps at 20', () => {
  assert.deepEqual(validHistory(null),[])
  assert.deepEqual(validHistory([null,{}, {id:1,date:'today',mode:'bad'}]),[])
  const entries=Array.from({length:22},(_,i)=>({id:i,date:'today',mode:'discount',fields:{price:'100',discount:'8'},final:1}))
  const cleaned=validHistory(entries)
  assert.equal(cleaned.length,20)
  assert.equal(cleaned[0].final,8000)
})
